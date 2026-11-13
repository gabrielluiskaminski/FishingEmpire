<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    // Exemplo documentado: R$ 15,00; grátis para mercadorias a partir de R$ 200,00.
    private const SHIPPING_CENTS = 1500;
    private const FREE_SHIPPING_FROM_CENTS = 20000;

    public function checkout(User $user, int $addressId): Order
    {
        return DB::transaction(function () use ($user, $addressId) {
            $address = $user->addresses()->findOrFail($addressId);
            $cart = $user->cart()->lockForUpdate()->first();
            if (!$cart) {
                throw ValidationException::withMessages(['cart' => 'Carrinho vazio.']);
            }

            $items = $cart->cartItems()->orderBy('product_id')->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Carrinho vazio.']);
            }

            $lines = [];
            $subtotalCents = 0;
            foreach ($items as $item) {
                // Bloqueio para garantir estoque consistente entre checkouts simultâneos.
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Produto indisponível (ID: ' . $item->product_id . ').',
                    ]);
                }
                if ($item->quantity < 1 || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Estoque insuficiente para ' . $product->name . '.',
                    ]);
                }

                $unitCents = Money::cents((string) $product->price);
                $subtotalCents += $unitCents * $item->quantity;
                $lines[] = [$item, $product, $unitCents];
            }

            $shippingCents = $subtotalCents >= self::FREE_SHIPPING_FROM_CENTS ? 0 : self::SHIPPING_CENTS;
            $order = Order::create([
                'user_id' => $user->id,
                'shipping_address' => $address->address,
                'shipping_number' => $address->house_number,
                'shipping_cep' => $address->cep,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_cost' => Money::format($shippingCents),
                'status' => 'pendente',
                'total' => Money::format($subtotalCents + $shippingCents),
                'paid_at' => null,
            ]);

            foreach ($lines as [$item, $product, $unitCents]) {
                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => Money::format($unitCents),
                    'quantity' => $item->quantity,
                ]);
                $product->decrement('stock', $item->quantity);
            }

            $cart->cartItems()->delete();
            return $order->load('orderItems');
        }, 3);
    }

    public function myOrders(User $user): LengthAwarePaginator
    {
        return $user->orders()->latest('id')->paginate(12);
    }

    public function findVisibleOrder(User $user, int $id): Order
    {
        $query = Order::with('orderItems')->whereKey($id);
        if (!$user->is_admin) $query->where('user_id', $user->id);
        return $query->firstOrFail();
    }

    public function pay(User $user, int $id): Order
    {
        return DB::transaction(function () use ($user, $id) {
            // Apenas o dono pode confirmar o pagamento simulado.
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
            if ((int) $order->user_id !== (int) $user->id) {
                abort(403, 'Somente o dono pode confirmar o pagamento.');
            }
            if ($order->status !== 'pendente') {
                abort(409, 'Somente pedidos pendentes podem ser pagos.');
            }
            $order->update(['status' => 'pago', 'paid_at' => now()]);
            return $order->load('orderItems');
        });
    }

    public function cancel(User $user, int $id): Order
    {
        return DB::transaction(function () use ($user, $id) {
            $query = Order::whereKey($id);
            if (!$user->is_admin) $query->where('user_id', $user->id);
            $order = $query->lockForUpdate()->firstOrFail();
            if ($order->status !== 'pendente') {
                abort(409, 'Somente pedidos pendentes podem ser cancelados.');
            }

            foreach ($order->orderItems()->orderBy('product_id')->get() as $item) {
                Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail()
                    ->increment('stock', $item->quantity);
            }
            $order->update(['status' => 'cancelado']);
            return $order->load('orderItems');
        }, 3);
    }

    public function adminOrders(?string $status): LengthAwarePaginator
    {
        return Order::query()->with('user:id,name,email')->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')->paginate(12);
    }

    public function changeStatus(int $id, string $status): Order
    {
        return DB::transaction(function () use ($id, $status) {
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
            $next = ['pago' => 'enviado', 'enviado' => 'entregue'];
            if (($next[$order->status] ?? null) !== $status) {
                abort(409, 'Transição de status inválida.');
            }
            $order->update(['status' => $status]);
            return $order->load('orderItems');
        });
    }
}
