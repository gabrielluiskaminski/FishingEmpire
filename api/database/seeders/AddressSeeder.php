<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $enderecos = [
            [
                'email' => 'ana@pescalivre.test',
                'address' => 'Rua das Garças',
                'house_number' => '120',
                'city' => 'Curitiba',
                'state' => 'PR',
                'cep' => '80000000',
            ],
            [
                'email' => 'bruno@pescalivre.test',
                'address' => 'Avenida dos Pescadores',
                'house_number' => '55',
                'city' => 'Florianópolis',
                'state' => 'SC',
                'cep' => '88000000',
            ],
            [
                'email' => 'carla@pescalivre.test',
                'address' => 'Rua do Lago',
                'house_number' => '310',
                'city' => 'São Paulo',
                'state' => 'SP',
                'cep' => '01000000',
            ],
        ];

        foreach ($enderecos as $dados) {
            $usuario = User::where('email', $dados['email'])->firstOrFail();

            Address::firstOrCreate(
                [
                    'user_id' => $usuario->id,
                    'address' => $dados['address'],
                    'house_number' => $dados['house_number'],
                ],
                [
                    'city' => $dados['city'],
                    'state' => $dados['state'],
                    'cep' => $dados['cep'],
                ],
            );
        }
    }
}
