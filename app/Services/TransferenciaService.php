<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transacao;
use App\Models\Registro;
use App\Repositories\TransacaoRepository;
use App\Repositories\CarteiraRepository;
use App\Repositories\RegistroRepository;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidTransferException;
use App\Models\Extrato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TransferenciaService
{
    public function __construct(
        private TransacaoRepository $transacaoRepo,
        private CarteiraRepository $carteiraRepo,
        private RegistroRepository $registroRepo,
    ) {}

    public function getLastsTransfers()
    {
        return $this->transacaoRepo->getLastsTransfers(Auth::user()->id);
    }

    public function getAllTransfers($filters = [])
    {
        try{
            $page = (int)($filters['page'] ?? 1);
            $perPage = (int)($filters['per_page'] ?? 15);
            $usuario = $filters['nome_usuario'] ?? null;
            $tipo = $filters['tipo'] ?? null;
            $tipo = $tipo == 'credito' ? 'credito' : ($tipo == 'debito' ? 'debito' : null);
            $startDate = $filters['data_inicio'] ?? null;
            $endDate = $filters['data_fim'] ?? null;
    
            return $this->transacaoRepo->getUserTransacoes(
                Auth::user()->id,
                $page,
                $perPage,
                $usuario,
                $tipo,
                $startDate,
                $endDate
            );

        }
        catch (\Exception $e) {
            Log::error('Erro ao obter extrato completo', [
                'user_id' => Auth::user()->id,
                'filters' => $filters,
                'error' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Ocorreu um erro ao obter o extrato completo. Tente novamente mais tarde.',
            ];
        }
    }

    /**
     * Executar transferência entre usuários
     */
    public function transfer(User $de, User $para, float $amount): array
    {
        try {
            // Validação básica
            $this->validateTransfer($de, $para, $amount);
            // Transação atômica do banco
            return DB::transaction(function () use ($de, $para, $amount) {
                return $this->executeTransfer($de, $para, $amount);
            });
        } catch (InvalidTransferException $e) {
            $this->transacaoRepo->create([
                'de_user_id' => $de->id,
                'para_user_id' => $para->id,
                'valor' => $amount ?? 0.01,
                'status' => 'falhou',
                'motivo_falha' => $e->getMessage(),
                'tipo' => 'debito',
            ]);

            Log::error('Transferencia inválida', [
                'de' => $de->id,
                'para' => $para->id,
                'valor' => $amount,
                'error' => $e,
            ]);

            throw $e;
        }
        catch (\Exception $e) {
            Log::error('Erro ao processar transferência', [
                'de' => $de->id,
                'para' => $para->id,
                'valor' => $amount ?? 0.01,
                'error' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Ocorreu um erro ao processar a transferência. Tente novamente mais tarde.',
            ];
        }

    }

    /**
     * Executar a transferência (dentro da transação)
     */
    private function executeTransfer(User $from, User $to, float $amount): array
    {
        // 1. Verificar saldo com lock
        if (!$this->carteiraRepo->hasSufficientBalance($from->id, $amount)) {
            throw new InsufficientBalanceException(
                'Saldo insuficiente para realizar a transferência'
            );
        }

        // 2. Criar transação
        $transacao = $this->transacaoRepo->create([
            'de_user_id' => $from->id,
            'para_user_id' => $to->id,
            'valor' => $amount,
            'status' => 'pendente',
            'tipo' => 'debito',
        ]);

        // 3. Criar Registro de transferência
        $extrato = Extrato::create([
            'transacao_id' => $transacao->id,
            'status' => 'pendente',
            'descricao' => "Transferencia de {$from->email} para {$to->email} - R\$ {$amount}",
        ]);

        // 4. Criar linhas do ledger (dupla entrada)
        // Débito da carteira de saída
        $this->registroRepo->createDebit(
            $extrato,
            $from->carteira->id,
            $amount,
            'Transferência para '.$to->email
        );

        // Crédito da carteira de entrada
        $this->registroRepo->createCredit(
            $extrato,
            $to->carteira->id,
            $amount,
            'Crédito transferido de '.$from->email
        );

        // 5. Verificar se o journal está balanceado
        if (!$this->registroRepo->isJournalBalanced($extrato->id)) {
            throw new \Exception(
                'Falha na validação contábil'
            );
        }

        // 6. Atualizar saldos (denormalized)
        $this->carteiraRepo->decrement($from->carteira->id, $amount);
        $this->carteiraRepo->increment($to->carteira->id, $amount);

        // 7. Marcar como completa
        $this->transacaoRepo->update($transacao->id, [
            'status' => 'concluida',
            'completed_at' => now(),
        ]);

        $extrato->update(['status' => 'concluida']);

        Log::info('Transferência realizada com sucesso', [
            'transacao_id' => $transacao->id,
            'de_user_id' => $from->id.' ('.$from->email.')',
            'para_user_id' => $to->id.' ('.$to->email.')',
            'valor' => $amount,
        ]);

        return [
            'success' => true,
            'message' => 'Transferência realizada com sucesso. Novo saldo: R$ '.$this->carteiraRepo->getBalance($from->carteira->id)
        ];
    }

    /**
     * Validar transferência antes de executar
     */
    private function validateTransfer(User $from, User $to, float $amount): void
    {
        // Verificar se é o mesmo usuário
        if ($from->id === $to->id) {
            throw new InvalidTransferException('Não é possível transferir para si mesmo');
        }

        // Verificar se valor é válido
        if ($amount <= 0) {
            throw new InvalidTransferException('Valor deve ser maior que zero');
        }

        // Verificar se o valor tem até 2 casas decimais
        if ($amount != round($amount, 2)) {
            throw new InvalidTransferException('Valor deve ter no máximo 2 casas decimais');
        }

        // Verificar se usuário de destino existe
        if (!$to || !$to->exists) {
            throw new InvalidTransferException('Usuário destinatário não encontrado');
        }
    }
}