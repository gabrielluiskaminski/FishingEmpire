<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Services\CartService;
use App\Http\Resources\CartResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        $cart = $this->resolveCart($request, $cartService);

        return $this->cartResponse($cart, $request);
    }

    public function store(StoreCartItemRequest $request, CartService $cartService)
    {
        $cart = $this->resolveCart($request, $cartService);
        
        $data = $request->validated();

        $cartService->addItem($cart, $data['product_id'], $data['quantity']);

        return $this->cartResponse($cart, $request);
    }

    public function update(UpdateCartItemRequest $request, CartService $cartService, int $item)
    {  
        $cart = $this->resolveCart($request, $cartService);

        $data = $request->validated();

        $cartService->updateItem($cart, $item, $data['quantity']);

        return $this->cartResponse($cart, $request);
    }

    public function destroy(Request $request, CartService $cartService, int $item) 
    {
        $cart = $this->resolveCart($request, $cartService);

        $cartService->removeItem($cart, $item);

        return $this->cartResponse($cart, $request);
    }

    private function resolveCart (Request $request, CartService $cartService): Cart
   {
        $user = $request->user('sanctum');

        if($user){
            return $cartService->getOrCreateUserCart($user);
        }

        return $cartService-> getOrCreateGuestCart($request->cookie('guest_token'));
   }

    private function cartResponse(Cart $cart, Request $request): JsonResponse
    {
        $cart->load('cartItems.product');

        $response = (new CartResource($cart))->response()->setStatusCode(200);

        if($cart->user_id === null){
            $response->cookie(
                'guest_token',
                $cart->guest_token,
                10080,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            );
        }

        return $response;
    }

}
