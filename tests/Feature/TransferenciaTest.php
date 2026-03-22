<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Carteira;

class TransferenciaTest extends TestCase
{

    use RefreshDatabase;

    public function test_saldo_novo_usuario(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);


        $response = $this
            ->actingAs($user)
            ->get('/api/saldo');

        $response->assertJson(['data' => 1000.00]);
        $response->assertStatus(200);

    }

    public function test_erro_transferencia_user_sem_carteira(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);

        $user2 = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/api/transferencia', [
                'email' => $user2->email,
                'valor' => 200.00
            ]);

        $response->assertStatus(422);

    }

    public function test_api_ultimas_transferencias(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);

        $response = $this
            ->actingAs($user)
            ->get('/api/ultimas-transferencias');

        $response->assertStatus(200);

    }

    public function test_transferencia_dashboard(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);

        $user2 = User::factory()->create();
        $carteira2 = Carteira::create(['user_id' => $user2->id,'valor_atual' => 1000]);

        $response = $this
            ->actingAs($user)
            ->from('/dashboard')
            ->post('/api/transferencia', [
                'email' => $user2->email,
                'valor' => 15.00
            ]);

        $response->assertStatus(200);

    }

    public function test_transferencia_extrato(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);

        $user2 = User::factory()->create();
        $carteira2 = Carteira::create(['user_id' => $user2->id,'valor_atual' => 1000]);

        $response = $this
            ->actingAs($user)
            ->from('/extrato')
            ->post('/api/transferencia', [
                'email' => $user2->email,
                'valor' => 15.00
            ]);

        $response->assertStatus(200);

    }

    public function test_erro_transferencia_email_invalido(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);

        $response = $this
            ->actingAs($user)
            ->post('/api/transferencia', [
                'email' => 'email_invalido@example.com',
                'valor' => 200.00
            ]);

        $response->assertStatus(422);

    }

    public function test_saldo_apos_transferencia(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id,'valor_atual' => 1000]);
        $valorAtual = $carteira->valor_atual;

        $user2 = User::factory()->create();
        $carteira2 = Carteira::create(['user_id' => $user2->id,'valor_atual' => 1000]);

        $response = $this
            ->actingAs($user)
            ->post('/api/transferencia', [
                'email' => $user2->email,
                'valor' => 200.00
            ]);

        $user->refresh()->carteira()->first()->refresh();
        $user2->refresh()->carteira()->first()->refresh();

        dump($user->carteira()->first()->valor_atual);
        dump($user2->carteira()->first()->valor_atual);

        $this->assertEquals($valorAtual - 200.00, $user->carteira()->first()->valor_atual);
        $this->assertEquals($valorAtual + 200.00, $user2->carteira()->first()->valor_atual);
        $response->assertStatus(200);

    }
}
