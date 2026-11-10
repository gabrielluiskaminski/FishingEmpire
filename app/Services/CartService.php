<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
   public function getOrCreateGuestCart(?string $guestToken): Cart
   {
        if ($guestToken) {
            $cart= Cart::where('guest_token', $guestToken)->first();

            if ($cart) {
                return $cart;
            }
        }

        return Cart::create([
            'guest_token' => Str::random(64),
        ]);
   }

   public function getOrCreateUserCart(User $user): Cart
   {
        return Cart::firstOrCreate([
            'user_id' => $user->id
        ]);
   }

   public function addItem(Cart $cart, int $productId, int $quantity): CartItem
   {
        return DB::transaction(function () use  ($cart, $productId, $quantity) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            if ($quantity < 1){
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade adicionada deve ser maior que zero.'
                ]);
            }

            $product = Product::where('is_active', true)->findOrFail($productId);

            $item = $cart->cartItems()->where('product_id', $productId)->first();

            if (!$item){
                $item = $cart->cartItems()->make([
                    'product_id' => $productId
                ]);
            }

            $item->setRelation('product', $product);
            
            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            return $this->saveQuantity($item, $newQuantity);
        });
   }

   public function updateItem(Cart $cart, int $itemId, int $quantity): CartItem 
   {
        return DB::transaction(function () use ($cart, $itemId, $quantity) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            $item = $cart->cartItems()->with('product')->findOrFail($itemId);

            if(!$item->product->is_active){
                throw ValidationException::withMessages([
                    'product' => 'Este produto está indisponível.'
                ]);
            }

            return $this->saveQuantity($item, $quantity);
        });
   }

   public function removeItem(Cart $cart, int $itemId): void
   {
        DB::transaction(function () use ($cart, $itemId) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            $item = $cart->cartItems()->findOrFail($itemId);

            $item->delete();
        });
   }

   public function mergeGuestCart (User $user, ?string $guestToken): void
   {
        if (!$guestToken){
            return;
        }

        DB::transaction(function () use ($user, $guestToken){
            $guestCart = Cart::where('guest_token', $guestToken)->lockForUpdate()->first();

            if (!$guestCart){
                return;
            }

            $userCart = $this->getOrCreateUserCart($user);

            foreach ($guestCart->cartItems as $item) {
                $this->addItem($userCart, $item->product_id, $item->quantity);
            }

            $guestCart->delete();
        });
   }

   private function saveQuantity(CartItem $item, int $quantity): CartItem
   {
        if ($quantity < 1 || $quantity > $item->product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantidade inválida ou estoque insuficiente.'
                ]);
            }

        $item->fill(['quantity' => $quantity]);
        $item->save();
            
        return $item;
   }
}
