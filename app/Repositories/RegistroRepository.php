<?php

namespace App\Repositories;

use App\Models\Extrato;
use App\Models\Registro;

class RegistroRepository
{
    public function __construct(
        private Extrato $model
    ) {}

    /**
     * Criar entrada de débito
     */
    public function createDebit(
        Registro $Registro,
        int $walletId,
        float $amount,
        string $description = null
    ): Extrato {
        return $this->model->create([
            'registro_id' => $Registro->id,
            'carteira_id' => $walletId,
            'debito' => $amount,
            'credito' => 0,
            'descricao' => $description,
        ]);
    }

    /**
     * Criar entrada de crédito
     */
    public function createCredit(
        Registro $Registro,
        int $walletId,
        float $amount,
        string $description = null
    ): Extrato {
        return $this->model->create([
            'registro_id' => $Registro->id,
            'carteira_id' => $walletId,
            'debito' => 0,
            'credito' => $amount,
            'descricao' => $description,
        ]);
    }

    /**
     * Obter todas as entries de um registro
     */
    public function getByRegistro(int $registroId): array
    {
        return $this->model
            ->where('registro_id', $registroId)
            ->get()
            ->toArray();
    }

    /**
     * Obter total de débitos e créditos de um registro
     */
    public function getJournalTotals(int $registroId): array
    {
        $entries = $this->model
            ->where('registro_id', $registroId)
            ->get();

        return [
            'total_debit' => $entries->sum('debito'),
            'total_credit' => $entries->sum('credito'),
        ];
    }

    /**
     * Verificar se journal está balanceado (debit = credit)
     */
    public function isJournalBalanced(int $registroId): bool
    {
        $totals = $this->getJournalTotals($registroId);
        
        return $totals['total_debit'] === $totals['total_credit'];
    }
}