<?php

namespace App\Repositories;

use App\Models\Transacao;
use Illuminate\Pagination\Paginator;

class TransacaoRepository
{
    public function __construct(
        private Transacao $model
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
        ?string $type = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Paginator {
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

        // Filtro por período
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query
            ->with(['deUser:id,name,email', 'paraUser:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Obter últimas N transações
     */
    public function getRecentTransacoes(int $userId, int $limit = 5): array
    {
        return $this->model
            ->where(function ($q) use ($userId) {
                $q->where('de_user_id', $userId)
                  ->orWhere('para_user_id', $userId);
            })
            ->where('status', 'concluida')
            ->with(['deUser:id,name,email', 'paraUser:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
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