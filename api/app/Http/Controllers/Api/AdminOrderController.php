<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request, OrderService $orders)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pendente', 'pago', 'enviado', 'entregue', 'cancelado'])],
        ]);
        return OrderResource::collection($orders->adminOrders($filters['status'] ?? null));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, OrderService $orders, int $id)
    {
        return new OrderResource($orders->changeStatus($id, $request->validated('status')));
    }
}
