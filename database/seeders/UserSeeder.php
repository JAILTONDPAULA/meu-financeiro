<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuário padrão para testar as rotas de autenticação.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'jailton.viana2@hotmail.com'],
            [
                'name' => 'Jailton Viana',
                'password' => 'abc1234', // O cast 'hashed' do model faz o hash.
                'email_verified_at' => now(),
            ]
        );
    }
}
