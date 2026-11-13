<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(int $stock = 3): array
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Teste']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vara de teste',
            'price' => '10.50',
            'stock' => $stock,
            'is_active' => true,
        ]);
        $address = $user->addresses()->create([
            'address' => 'Rua A', 'house_number' => '10', 'cep' => '80000000',
            'city' => 'Curitiba', 'state' => 'PR',
        ]);
        $cart = $user->cart()->create();
        $cart->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        Sanctum::actingAs($user);
        return [$user, $product, $address, $cart];
    }

    public function test_checkout_locks_stock_and_clears_cart(): void
    {
        [$user, $product, $address, $cart] = $this->fixture();
        $response = $this->postJson('/api/orders', ['address_id' => $address->id]);
        $response->assertCreated()->assertJsonPath('data.status', 'pendente')
            ->assertJsonPath('data.total', '36.00'); // 21.00 + 15.00 de frete
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id, 'product_name' => 'Vara de teste',
            'quantity' => 2, 'product_price' => '10.50',
        ]);
    }

    public function test_insufficient_stock_does_not_create_order(): void
    {
        [$user, $product, $address, $cart] = $this->fixture(stock: 1);
        $this->postJson('/api/orders', ['address_id' => $address->id])->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'quantity' => 2]);
    }

    public function test_customer_cannot_use_admin_routes(): void
    {
        $this->fixture();
        $this->getJson('/api/admin/orders')->assertForbidden();
    }
    public function test_checkout_rejects_an_address_from_another_user(): void
    {
        [$user] = $this->fixture();
        $other = User::factory()->create();
        $otherAddress = $other->addresses()->create([
            'address' => 'Rua B', 'house_number' => '20', 'cep' => '80000001',
            'city' => 'Curitiba', 'state' => 'PR',
        ]);

        $this->postJson('/api/orders', ['address_id' => $otherAddress->id])
            ->assertUnprocessable()->assertJsonValidationErrors('address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_payment_updates_status_and_paid_at_without_reducing_stock_again(): void
    {
        [$user, $product, $address] = $this->fixture();
        $orderId = $this->postJson('/api/orders', ['address_id' => $address->id])
            ->assertCreated()->json('data.id');

        $this->postJson("/api/orders/{$orderId}/pay")
            ->assertOk()->assertJsonPath('data.status', 'pago');
        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'pago']);
        $this->assertNotNull(\App\Models\Order::findOrFail($orderId)->paid_at);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->postJson("/api/orders/{$orderId}/pay")->assertStatus(409);
    }

    public function test_cancel_pending_order_restores_stock(): void
    {
        [$user, $product, $address, $cart] = $this->fixture();
        $orderId = $this->postJson('/api/orders', ['address_id' => $address->id])
            ->assertCreated()->json('data.id');
        $this->postJson("/api/orders/{$orderId}/cancel")
            ->assertOk()->assertJsonPath('data.status', 'cancelado');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->postJson("/api/orders/{$orderId}/cancel")->assertStatus(409);
    }

    public function test_another_user_cannot_pay_an_order(): void
    {
        [$user, $product, $address] = $this->fixture();
        $orderId = $this->postJson('/api/orders', ['address_id' => $address->id])
            ->assertCreated()->json('data.id');

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/orders/{$orderId}/pay")->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'pendente']);
    }

}
