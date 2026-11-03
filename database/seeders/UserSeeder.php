<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $senha = Hash::make('Senha123!');

        $usuarios = [
            ['name' => 'Administrador da Loja', 'email' => 'admin@pescalivre.test'],
            ['name' => 'Ana Silva', 'email' => 'ana@pescalivre.test'],
            ['name' => 'Bruno Costa', 'email' => 'bruno@pescalivre.test'],
            ['name' => 'Carla Lima', 'email' => 'carla@pescalivre.test'],
        ];

        foreach ($usuarios as $dados) {
            $usuario = User::firstOrCreate(
                ['email' => $dados['email']],
                ['name' => $dados['name'], 'password' => $senha],
            );

            if ($dados['email'] === 'admin@pescalivre.test' && !$usuario->is_admin) {
                $usuario->forceFill(['is_admin' => true])->save();
            }
        }
    }
}
