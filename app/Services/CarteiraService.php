<?php

namespace App\Services;

use App\Models\User;
use App\Models\Carteira;
use App\Repositories\CarteiraRepository;
use App\Repositories\TransacaoRepository;

class CarteiraService
{
    public function __construct(
        private CarteiraRepository $carteiraRepo,
        private TransacaoRepository $transacaoRepo,
    ) {}

    /**
     * Obter saldo atual do usuário
     */
    public function getBalance(User $user): float
    {
        return $this->carteiraRepo->getBalance($user->id) ?? 0.00;
    }

    /**
     * Obter informações completas da carteira
     */
    public function getWalletInfo(User $user): array
    {
        $wallet = $this->carteiraRepo->getByUser($user);
        $recentTransactions = $this->transacaoRepo->getRecentTransacoes($user->id, 5);

        return [
            'balance' => $wallet?->balance ?? 0.00,
            'recent_transactions' => $recentTransactions,
            'updated_at' => $wallet?->updated_at,
        ];
    }

    /**
     * Criar carteira para novo usuário
     */
    public function createWallet(User $user, float $initialBalance = 1000.00): Carteira
    {
        return $this->carteiraRepo->create($user->id, $initialBalance);
    }

    /**
     * Obter histórico de transações com filtros
     */
    public function getTransactionHistory(
        User $user,
        int $page = 1,
        int $perPage = 15,
        ?string $type = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        return $this->transacaoRepo->getUserTransacoes(
            $user->id,
            $page,
            $perPage,
            $type,
            $startDate,
            $endDate
        );
    }
}