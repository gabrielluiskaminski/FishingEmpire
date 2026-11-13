<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orders)
    {
        return OrderResource::collection($orders->myOrders($request->user()));
    }

    public function store(StoreOrderRequest $request, OrderService $orders)
    {
        $order = $orders->checkout($request->user(), (int) $request->validated('address_id'));
        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Request $request, OrderService $orders, int $id)
    {
        return new OrderResource($orders->findVisibleOrder($request->user(), $id));
    }

    public function pay(Request $request, OrderService $orders, int $id)
    {
        return new OrderResource($orders->pay($request->user(), $id));
    }

    public function cancel(Request $request, OrderService $orders, int $id)
    {
        return new OrderResource($orders->cancel($request->user(), $id));
    }
}
