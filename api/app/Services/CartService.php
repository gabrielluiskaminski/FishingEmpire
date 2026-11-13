<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateGuestCart(?string $guestToken): Cart
    {
        if ($guestToken) {
            $cart = Cart::where('guest_token', $guestToken)->whereNull('user_id')->first();
            if ($cart) return $cart;
        }

        return Cart::create(['guest_token' => Str::random(64)]);
    }

    public function getOrCreateUserCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(Cart $cart, int $productId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $productId, $quantity) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($quantity < 1) {
                throw ValidationException::withMessages(['quantity' => 'Quantidade deve ser maior que zero.']);
            }
            $product = Product::where('is_active', true)->findOrFail($productId);
            $item = $cart->cartItems()->where('product_id', $productId)->first();
            if (!$item) {
                $item = $cart->cartItems()->make(['product_id' => $productId]);
            }
            $item->setRelation('product', $product);
            return $this->saveQuantity($item, ($item->quantity ?? 0) + $quantity);
        });
    }

    public function updateItem(Cart $cart, int $itemId, int $quantity): ?CartItem
    {
        return DB::transaction(function () use ($cart, $itemId, $quantity) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $item = $cart->cartItems()->with('product')->findOrFail($itemId);
            if ($quantity === 0) {
                $item->delete();
                return null;
            }
            if (!$item->product || !$item->product->is_active) {
                throw ValidationException::withMessages(['product' => 'Produto indisponível.']);
            }
            return $this->saveQuantity($item, $quantity);
        });
    }

    public function removeItem(Cart $cart, int $itemId): void
    {
        DB::transaction(function () use ($cart, $itemId) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $cart->cartItems()->findOrFail($itemId)->delete();
        });
    }

    public function mergeGuestCart(User $user, ?string $guestToken): array
    {
        if (!$guestToken) return [];

        return DB::transaction(function () use ($user, $guestToken) {
            $guestCart = Cart::whereNull('user_id')->where('guest_token', $guestToken)
                ->lockForUpdate()->first();
            if (!$guestCart) return [];

            $userCart = $this->getOrCreateUserCart($user);
            Cart::whereKey($userCart->id)->lockForUpdate()->firstOrFail();
            $warnings = [];

            foreach ($guestCart->cartItems()->orderBy('product_id')->get() as $guestItem) {
                $product = Product::whereKey($guestItem->product_id)->lockForUpdate()->first();
                if (!$product || !$product->is_active || $product->stock < 1) {
                    $warnings[] = [
                        'product_id' => $guestItem->product_id,
                        'message' => 'Produto indisponível. Item não transferido.',
                    ];
                    continue;
                }

                $item = $userCart->cartItems()->where('product_id', $product->id)->first();
                $requested = (int) ($item->quantity ?? 0) + (int) $guestItem->quantity;
                $accepted = min($requested, (int) $product->stock);
                if ($accepted < $requested) {
                    $warnings[] = [
                        'product_id' => $product->id,
                        'message' => 'Quantidade limitada ao estoque disponível.',
                        'requested' => $requested,
                        'accepted' => $accepted,
                    ];
                }
                if (!$item) {
                    $item = $userCart->cartItems()->make(['product_id' => $product->id]);
                }
                $item->setRelation('product', $product);
                $this->saveQuantity($item, $accepted);
            }

            // Necessário: a FK cart_items.cart_id usa cascadeOnDelete.
            $guestCart->delete();
            return $warnings;
        });
    }

    private function saveQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity < 1 || $quantity > $item->product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Quantidade inválida ou estoque insuficiente.']);
        }
        $item->quantity = $quantity;
        $item->save();
        return $item;
    }
}
