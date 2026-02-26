<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StoreOrdersController extends Controller
{
    /**
     * سجل طلبات المتجر للمستخدم (للموبايل).
     */
    public function index(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $orders = StoreOrder::query()
            ->where('user_id', $user->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $items = $orders->map(function (StoreOrder $order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number ?? '',
                'subtotal' => (float) ($order->subtotal ?? 0),
                'shipping_cost' => (float) ($order->shipping_cost ?? 0),
                'discount_amount' => (float) ($order->discount_amount ?? 0),
                'total' => (float) ($order->total ?? 0),
                'status' => $order->status ?? 'pending',
                'payment_status' => $order->payment_status ?? 'pending',
                'paid_at' => $order->paid_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'items_count' => $order->items->sum('quantity'),
                'items_summary' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_id' => $i->product_id,
                    'product_name' => $i->product_name ?? '—',
                    'quantity' => (int) ($i->quantity ?? 1),
                    'price' => (float) ($i->price ?? 0),
                    'total' => (float) ($i->total ?? 0),
                ])->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'orders' => $items,
        ]);
    }
}
