<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $nomes = [
            'Varas',
            'Molinetes e Carretilhas',
            'Iscas',
            'Anzóis e Terminais',
            'Linhas',
            'Acessórios',
        ];

        foreach ($nomes as $nome) {
            Category::firstOrCreate(
                ['name' => $nome],
                ['description' => "Produtos de pesca da categoria {$nome}."],
            );
        }
    }
}
