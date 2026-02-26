<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    /**
     * سجل طلبات/مشتريات المستخدم (للموبايل).
     */
    public function index(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with('items.course')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $items = $orders->map(function (Order $order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number ?? '',
                'subtotal' => (float) ($order->subtotal ?? 0),
                'discount' => (float) ($order->discount ?? 0),
                'total' => (float) ($order->total ?? 0),
                'status' => $order->status ?? 'pending',
                'paid_at' => $order->paid_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'items_count' => $order->items->count(),
                'items_summary' => $order->items->map(fn ($i) => [
                    'title' => $i->course?->title ?? '—',
                    'quantity' => 1,
                    'price' => (float) ($i->price ?? 0),
                ])->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'orders' => $items,
        ]);
    }
}
