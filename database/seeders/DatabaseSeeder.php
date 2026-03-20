<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //User::factory(10)->create();

        for ($i = 1; $i <= 10; $i++) {
            $user = User::factory()->create([
                'name' => "User $i",
                'email' => "user$i@example.com",
            ]);

            $user->carteira()->create([
                'user_id' => $user->id,
                'valor_atual' => 1000.00,
            ]);
        }

    }
}
