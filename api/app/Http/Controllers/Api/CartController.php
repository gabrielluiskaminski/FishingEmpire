<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, CartService $cartService): JsonResponse
    {
        return $this->cartResponse($this->resolveCart($request, $cartService), $request);
    }

    public function store(StoreCartItemRequest $request, CartService $cartService): JsonResponse
    {
        $cart = $this->resolveCart($request, $cartService);
        $data = $request->validated();
        $cartService->addItem($cart, $data['product_id'], $data['quantity']);
        return $this->cartResponse($cart, $request);
    }

    public function update(UpdateCartItemRequest $request, CartService $cartService, int $item): JsonResponse
    {
        $cart = $this->resolveCart($request, $cartService);
        $cartService->updateItem($cart, $item, (int) $request->validated('quantity'));
        return $this->cartResponse($cart, $request);
    }

    public function destroy(Request $request, CartService $cartService, int $item): JsonResponse
    {
        $cart = $this->resolveCart($request, $cartService);
        $cartService->removeItem($cart, $item);
        return $this->cartResponse($cart, $request);
    }

    private function resolveCart(Request $request, CartService $cartService): Cart
    {
        $user = $request->user('sanctum');
        return $user
            ? $cartService->getOrCreateUserCart($user)
            : $cartService->getOrCreateGuestCart($request->cookie('guest_token'));
    }

    private function cartResponse(Cart $cart, Request $request): JsonResponse
    {
        $cart->load('cartItems.product');
        $response = (new CartResource($cart))->response()->setStatusCode(200);
        if ($cart->user_id === null) {
            $response->cookie('guest_token', $cart->guest_token, 10080, '/', null,
                $request->isSecure(), true, false, 'lax');
        }
        return $response;
    }
}
