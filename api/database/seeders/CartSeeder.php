<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $ana = User::where('email', 'ana@pescalivre.test')->firstOrFail();
        $carrinhoAna = Cart::firstOrCreate(['user_id' => $ana->id]);

        $this->adicionarItem($carrinhoAna, 'Óculos polarizados para pesca', 1);
        $this->adicionarItem($carrinhoAna, 'Anzol maruseigo nº 10 - 10 un.', 2);

        $tokenVisitante = hash_hmac('sha256', 'pescalivre-demo-guest', (string) config('app.key'));
        $carrinhoVisitante = Cart::firstOrCreate(['guest_token' => $tokenVisitante]);
        $this->adicionarItem($carrinhoVisitante, 'Isca de silicone shad 8 cm', 3);
    }

    private function adicionarItem(Cart $carrinho, string $nomeProduto, int $quantidade): void
    {
        $produto = Product::where('name', $nomeProduto)->firstOrFail();

        CartItem::firstOrCreate(
            ['cart_id' => $carrinho->id, 'product_id' => $produto->id],
            ['quantity' => $quantidade],
        );
    }
}
