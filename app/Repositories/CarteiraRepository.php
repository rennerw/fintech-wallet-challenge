<?php

namespace App\Repositories;

use App\Models\Carteira;
use App\Models\User;

class CarteiraRepository
{
    public function __construct(
        private Carteira $model
    ) {}

    /**
     * Obter carteira por ID
     */
    public function findById(int $id): ?Carteira
    {
        return $this->model->find($id);
    }

    /**
     * Obter carteira de um usuário
     */
    public function getByUser(User $user): ?Carteira
    {
        return $user->carteira;
    }

    /**
     * Obter carteira por user_id (com lock para evitar race condition)
     */
    public function getByUserIdWithLock(int $userId): ?Carteira
    {
        return $this->model
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Criar carteira
     */
    public function create(int $userId, float $initialBalance = 1000.00): Carteira
    {
        return $this->model->create([
            'user_id' => $userId,
            'valor_atual' => $initialBalance,
        ]);
    }

    /**
     * Incrementar saldo
     */
    public function increment(int $carteiraId, float $amount): bool
    {
        $carteira = $this->model->find($carteiraId);
        
        if (!$carteira) {
            return false;
        }

        return $carteira->increment('valor_atual', $amount) !== null;
    }

    /**
     * Decrementar saldo
     */
    public function decrement(int $carteiraId, float $amount): bool
    {
        $carteira = $this->model->find($carteiraId);
        
        if (!$carteira) {
            return false;
        }

        return $carteira->decrement('valor_atual', $amount) !== null;
    }

    /**
     * Verificar se usuário tem saldo suficiente
     */
    public function hasSufficientBalance(int $userId, float $amount): bool
    {
        $carteira = $this->getByUserIdWithLock($userId);
        
        return $carteira && $carteira->valor_atual >= $amount;
    }

    /**
     * Obter saldo atual
     */
    public function getBalance(int $userId): ?float
    {
        return $this->model
            ->where('user_id', $userId)
            ->value('valor_atual');
    }
}