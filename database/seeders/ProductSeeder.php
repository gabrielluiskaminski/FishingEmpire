<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [nome, preço decimal como string, estoque inicial]
        $catalogo = [
            'Varas' => [
                ['Vara telescópica 2,10 m', '109.90', 20],
                ['Vara de carbono 1,80 m', '249.90', 12],
                ['Vara para molinete 2,40 m', '189.90', 16],
                ['Vara para carretilha 1,68 m', '159.90', 18],
                ['Vara ultralight 1,50 m', '139.90', 8],
                ['Vara de praia 3,60 m', '299.90', 6],
            ],
            'Molinetes e Carretilhas' => [
                ['Molinete tamanho 1000', '119.90', 20],
                ['Molinete tamanho 3000', '189.90', 14],
                ['Molinete tamanho 5000', '269.90', 9],
                ['Carretilha perfil baixo 7.2:1', '329.90', 10],
                ['Carretilha perfil alto', '239.90', 7],
                ['Carretilha BFS ultraleve', '489.90', 5],
            ],
            'Iscas' => [
                ['Isca artificial meia-água 9 cm', '29.90', 40],
                ['Isca artificial de superfície 11 cm', '34.90', 35],
                ['Isca de silicone shad 8 cm', '16.90', 65],
                ['Spinner metálico 12 g', '19.90', 50],
                ['Colher oscilante 18 g', '17.90', 55],
                ['Kit de iscas soft 10 unidades', '59.90', 22],
            ],
            'Anzóis e Terminais' => [
                ['Anzol maruseigo nº 10 - 10 un.', '12.90', 75],
                ['Anzol wide gap nº 3/0 - 5 un.', '15.90', 70],
                ['Anzol circular nº 2/0 - 10 un.', '21.90', 40],
                ['Girador com snap - 10 un.', '9.90', 100],
                ['Chumbada oliva 20 g - 5 un.', '11.90', 55],
                ['Empate de aço 30 cm - 3 un.', '14.90', 60],
            ],
            'Linhas' => [
                ['Linha monofilamento 0,30 mm 100 m', '19.90', 40],
                ['Linha monofilamento 0,40 mm 100 m', '25.90', 35],
                ['Linha multifilamento 0,20 mm 150 m', '74.90', 28],
                ['Linha multifilamento 0,28 mm 150 m', '89.90', 25],
                ['Linha fluorcarbono 0,35 mm 50 m', '69.90', 18],
                ['Leader fluorcarbono 0,45 mm 20 m', '39.90', 20],
            ],
            'Acessórios' => [
                ['Óculos polarizados para pesca', '89.90', 15],
                ['Alicate de contenção de peixe', '79.90', 18],
                ['Pinça removedora de anzol', '29.90', 30],
                ['Alicate de bico longo', '44.90', 22],
                ['Bolsa de apetrechos', '129.90', 12],
                ['Caixa organizadora para iscas', '39.90', 34],
            ],
        ];

        foreach ($catalogo as $nomeCategoria => $itens) {
            $categoria = Category::where('name', $nomeCategoria)->firstOrFail();

            foreach ($itens as [$nome, $preco, $estoque]) {
                Product::firstOrCreate(
                    ['name' => $nome],
                    [
                        'category_id' => $categoria->id,
                        'description' => "Produto para pesca esportiva: {$nome}.",
                        'price' => $preco,
                        'stock' => $estoque,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
