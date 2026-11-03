<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = [
            [
                'email' => 'ana@pescalivre.test',
                'status' => 'pendente',
                'shipping_cost' => '18.90',
                'items' => [
                    'Vara telescópica 2,10 m' => 2,
                    'Isca artificial meia-água 9 cm' => 1,
                ],
            ],
            [
                'email' => 'bruno@pescalivre.test',
                'status' => 'pago',
                'shipping_cost' => '24.90',
                'items' => [
                    'Molinete tamanho 3000' => 1,
                    'Linha monofilamento 0,30 mm 100 m' => 1,
                ],
            ],
            [
                'email' => 'carla@pescalivre.test',
                'status' => 'cancelado',
                'shipping_cost' => '29.90',
                'items' => [
                    'Carretilha perfil baixo 7.2:1' => 1,
                    'Pinça removedora de anzol' => 1,
                ],
            ],
        ];

        DB::transaction(function () use ($pedidos): void {
            foreach ($pedidos as $dados) {
                $usuario = User::where('email', $dados['email'])->firstOrFail();
                $endereco = Address::where('user_id', $usuario->id)->firstOrFail();
                $produtosDoPedido = [];
                $subtotalCentavos = 0;

                foreach ($dados['items'] as $nome => $quantidade) {
                    $produto = Product::where('name', $nome)->firstOrFail();
                    $produtosDoPedido[] = [$produto, $quantidade];
                    // Evita aritmética com float para valores monetários.
                    $precoCentavos = (int) str_replace('.', '', (string) $produto->price);
                    $subtotalCentavos += $precoCentavos * $quantidade;
                }

                $freteCentavos = (int) str_replace('.', '', $dados['shipping_cost']);
                $totalCentavos = $subtotalCentavos + $freteCentavos;
                $total = sprintf('%d.%02d', intdiv($totalCentavos, 100), $totalCentavos % 100);

                $pedido = Order::firstOrCreate(
                    [
                        'user_id' => $usuario->id,
                        'shipping_address' => $endereco->address,
                        'shipping_number' => $endereco->house_number,
                    ],
                    [
                        'shipping_cep' => $endereco->cep,
                        'shipping_city' => $endereco->city,
                        'shipping_state' => $endereco->state,
                        'shipping_cost' => $dados['shipping_cost'],
                        'status' => $dados['status'],
                        'total' => $total,
                        'paid_at' => $dados['status'] === 'pago' ? now()->subDay() : null,
                    ],
                );

                foreach ($produtosDoPedido as [$produto, $quantidade]) {
                    $item = OrderItem::firstOrCreate(
                        ['order_id' => $pedido->id, 'product_id' => $produto->id],
                        [
                            'product_name' => $produto->name,
                            'quantity' => $quantidade,
                            'product_price' => $produto->price,
                        ],
                    );

                    if ($item->wasRecentlyCreated && $dados['status'] !== 'cancelado') {
                        $produto->decrement('stock', $quantidade);
                    }
                }
            }
        });
    }
}
