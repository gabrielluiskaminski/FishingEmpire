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

        // Ilustrações de catálogo criadas para os 36 produtos dos Seeders.
        $productImages = [
            'Vara telescópica 2,10 m' => 'products/01-vara-telescopica-2-10-m.webp',
            'Vara de carbono 1,80 m' => 'products/02-vara-de-carbono-1-80-m.webp',
            'Vara para molinete 2,40 m' => 'products/03-vara-para-molinete-2-40-m.webp',
            'Vara para carretilha 1,68 m' => 'products/04-vara-para-carretilha-1-68-m.webp',
            'Vara ultralight 1,50 m' => 'products/05-vara-ultralight-1-50-m.webp',
            'Vara de praia 3,60 m' => 'products/06-vara-de-praia-3-60-m.webp',
            'Molinete tamanho 1000' => 'products/07-molinete-tamanho-1000.webp',
            'Molinete tamanho 3000' => 'products/08-molinete-tamanho-3000.webp',
            'Molinete tamanho 5000' => 'products/09-molinete-tamanho-5000.webp',
            'Carretilha perfil baixo 7.2:1' => 'products/10-carretilha-perfil-baixo-7-2-1.webp',
            'Carretilha perfil alto' => 'products/11-carretilha-perfil-alto.webp',
            'Carretilha BFS ultraleve' => 'products/12-carretilha-bfs-ultraleve.webp',
            'Isca artificial meia-água 9 cm' => 'products/13-isca-artificial-meia-agua-9-cm.webp',
            'Isca artificial de superfície 11 cm' => 'products/14-isca-artificial-de-superficie-11-cm.webp',
            'Isca de silicone shad 8 cm' => 'products/15-isca-de-silicone-shad-8-cm.webp',
            'Spinner metálico 12 g' => 'products/16-spinner-metalico-12-g.webp',
            'Colher oscilante 18 g' => 'products/17-colher-oscilante-18-g.webp',
            'Kit de iscas soft 10 unidades' => 'products/18-kit-de-iscas-soft-10-unidades.webp',
            'Anzol maruseigo nº 10 - 10 un.' => 'products/19-anzol-maruseigo-no-10-10-un.webp',
            'Anzol wide gap nº 3/0 - 5 un.' => 'products/20-anzol-wide-gap-no-3-0-5-un.webp',
            'Anzol circular nº 2/0 - 10 un.' => 'products/21-anzol-circular-no-2-0-10-un.webp',
            'Girador com snap - 10 un.' => 'products/22-girador-com-snap-10-un.webp',
            'Chumbada oliva 20 g - 5 un.' => 'products/23-chumbada-oliva-20-g-5-un.webp',
            'Empate de aço 30 cm - 3 un.' => 'products/24-empate-de-aco-30-cm-3-un.webp',
            'Linha monofilamento 0,30 mm 100 m' => 'products/25-linha-monofilamento-0-30-mm-100-m.webp',
            'Linha monofilamento 0,40 mm 100 m' => 'products/26-linha-monofilamento-0-40-mm-100-m.webp',
            'Linha multifilamento 0,20 mm 150 m' => 'products/27-linha-multifilamento-0-20-mm-150-m.webp',
            'Linha multifilamento 0,28 mm 150 m' => 'products/28-linha-multifilamento-0-28-mm-150-m.webp',
            'Linha fluorcarbono 0,35 mm 50 m' => 'products/29-linha-fluorcarbono-0-35-mm-50-m.webp',
            'Leader fluorcarbono 0,45 mm 20 m' => 'products/30-leader-fluorcarbono-0-45-mm-20-m.webp',
            'Óculos polarizados para pesca' => 'products/31-oculos-polarizados-para-pesca.webp',
            'Alicate de contenção de peixe' => 'products/32-alicate-de-contencao-de-peixe.webp',
            'Pinça removedora de anzol' => 'products/33-pinca-removedora-de-anzol.webp',
            'Alicate de bico longo' => 'products/34-alicate-de-bico-longo.webp',
            'Bolsa de apetrechos' => 'products/35-bolsa-de-apetrechos.webp',
            'Caixa organizadora para iscas' => 'products/36-caixa-organizadora-para-iscas.webp',
        ];

        foreach ($catalogo as $nomeCategoria => $itens) {
            $categoria = Category::where('name', $nomeCategoria)->firstOrFail();

            foreach ($itens as [$nome, $preco, $estoque]) {
                $product = Product::firstOrCreate(
                    ['name' => $nome],
                    [
                        'category_id' => $categoria->id,
                        'description' => "Produto para pesca esportiva: {$nome}.",
                        'price' => $preco,
                        'stock' => $estoque,
                        'image_path' => $productImages[$nome],
                        'is_active' => true,
                    ],
                );

                // Preserva imagens enviadas pelo administrador e qualquer
                // alteração posterior de estoque ou preço nos produtos existentes.
                if (!$product->image_path) {
                    $product->update(['image_path' => $productImages[$nome]]);
                }
            }
        }
    }
}
