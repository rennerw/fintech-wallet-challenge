<?php

namespace App\Repositories;

use App\Models\Registro;
use App\Models\Transacao;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\String\s;

class TransacaoRepository
{
    public function __construct(
        private Transacao $model,
    ) {}

    /**
     * Criar uma nova transação
     */
    public function create(array $data): Transacao
    {
        return $this->model->create($data);
    }

    /**
     * Obter transações de um usuário com filtros
     */
    public function getUserTransacoes(
        int $userId,
        int $page = 1,
        int $perPage = 15,
        ?string $userName = null,
        ?string $type = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): LengthAwarePaginator {
        $query = $this->model
            ->where(function ($q) use ($userId) {
                $q->where('de_user_id', $userId)
                    ->orWhere('para_user_id', $userId);
            })
            ->where('status', 'concluida');

        // Filtro por tipo (débito/crédito)
        if ($type) {
            if ($type === 'debito') {
                $query->where('de_user_id', $userId);
            } elseif ($type === 'credito') {
                $query->where('para_user_id', $userId);
            }
        }
        if($userName) {
            $query->whereHas('deUser', function ($q) use ($userName) {
                $q->where('name', 'like', "%$userName%");
            })->orWhereHas('paraUser', function ($q) use ($userName) {
                $q->where('name', 'like', "%$userName%");
            });
        }

        // Filtro por período
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query
            ->with(['deUser:id,name,email', 'paraUser:id,name,email','extrato:transacao_id,id,descricao'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
    }

    /**
     * Obter últimas N transações
     */
    public function getLastsTransfers(int $userId): array
    {
        try{

            return [
                'success' => true,
                'data' => $this->model
                ->selectRaw('id, de_user_id, para_user_id, valor, 
                case 
                    when de_user_id = ? then \'debito\' 
                    when para_user_id = ? then \'credito\' 
                    else \'desconhecido\'
                end as tipo, status, created_at', [$userId, $userId])
                ->where(function ($q) use ($userId) {
                    $q->where('de_user_id', $userId)
                      ->orWhere('para_user_id', $userId);
                })
                ->where('status', 'concluida')
                ->with(['deUser:id,name,email', 'paraUser:id,name,email'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->toArray()];
        }
        catch (\Exception $e) {
            Log::error('Erro ao obter últimas transferências', [
                'user_id' => $userId,
                'error' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Ocorreu um erro ao obter as últimas transferências. Tente novamente mais tarde.',
            ];
        }
    }

    /**
     * Obter transação por ID
     */
    public function findById(int $id): ?Transacao
    {
        return $this->model->find($id);
    }

    /**
     * Atualizar transação
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->find($id)?->update($data) ?? false;
    }
}