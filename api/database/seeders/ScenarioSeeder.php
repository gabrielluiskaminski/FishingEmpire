<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Cenários obrigatórios do checklist: produto indisponível/sem estoque
 * e pedidos enviados/entregues. Dados exclusivos para desenvolvimento.
 */
class ScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (!Product::where('stock', 0)->exists()) {
                Product::where('name', 'Spinner metálico 12 g')->update(['stock' => 0]);
            }
            if (!Product::where('is_active', false)->exists()) {
                Product::where('name', 'Colher oscilante 18 g')->update(['is_active' => false]);
            }

            $cases = [
                ['email' => 'bruno@pescalivre.test', 'status' => 'enviado',
                 'product' => 'Linha multifilamento 0,28 mm 150 m'],
                ['email' => 'carla@pescalivre.test', 'status' => 'entregue',
                 'product' => 'Molinete tamanho 5000'],
            ];

            foreach ($cases as $case) {
                $user = User::where('email', $case['email'])->firstOrFail();
                if ($user->orders()->where('status', $case['status'])->exists()) {
                    continue;
                }

                $address = $user->addresses()->firstOrFail();
                $product = Product::where('name', $case['product'])
                    ->lockForUpdate()->firstOrFail();
                if (!$product->is_active || $product->stock < 1) {
                    continue;
                }

                $subtotal = Money::cents((string) $product->price);
                $shipping = $subtotal >= 20000 ? 0 : 1500;

                $order = Order::create([
                    'user_id' => $user->id,
                    'shipping_address' => $address->address,
                    'shipping_number' => $address->house_number,
                    'shipping_cep' => $address->cep,
                    'shipping_city' => $address->city,
                    'shipping_state' => $address->state,
                    'shipping_cost' => Money::format($shipping),
                    'status' => $case['status'],
                    'total' => Money::format($subtotal + $shipping),
                    'paid_at' => now()->subDays(2),
                ]);
                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => Money::format($subtotal),
                    'quantity' => 1,
                ]);
                $product->decrement('stock');
            }
        });
    }
}
