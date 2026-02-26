<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(
        protected PointsService $pointsService
    ) {}

    /**
     * ملخص نقاط المستخدم (الرصيد، الرتبة، النقاط للرتبة التالية)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $pointsForNextRank = $this->pointsService->getPointsForNextRank($user);
        $rankPosition = $this->pointsService->getUserRankPosition($user);

        return response()->json([
            'total_points' => (int) ($user->total_points ?? 0),
            'available_points' => (int) ($user->available_points ?? 0),
            'rank' => $user->rank ?? 'bronze',
            'rank_label' => $user->rank_label,
            'rank_color' => $user->rank_color,
            'points_for_next_rank' => $pointsForNextRank,
            'rank_position' => $rankPosition,
        ]);
    }

    /**
     * سجل المعاملات (صفحات)
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = PointTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $items = $transactions->map(function ($t) {
            return [
                'id' => $t->id,
                'points' => $t->points,
                'type' => $t->type,
                'type_label' => $t->type_label,
                'description' => $t->description,
                'created_at' => $t->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
        ]);
    }

    /**
     * المكافآت المتاحة
     */
    public function rewards(Request $request): JsonResponse
    {
        $user = $request->user();
        $availablePoints = (int) ($user->available_points ?? 0);

        $rewards = Reward::query()
            ->available()
            ->with('course:id,title,slug,cover_image')
            ->orderBy('points_required')
            ->get();

        $items = $rewards->map(function ($r) use ($availablePoints) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'type' => $r->type,
                'type_label' => $r->type_label,
                'points_required' => $r->points_required,
                'value' => $r->value,
                'image' => $r->image ? asset('storage/' . $r->image) : null,
                'course' => $r->course ? [
                    'id' => $r->course->id,
                    'title' => $r->course->title,
                    'slug' => $r->course->slug,
                    'thumbnail' => $r->course->cover_image ?? null,
                ] : null,
                'can_redeem' => $availablePoints >= $r->points_required,
            ];
        });

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * استبدال مكافأة
     */
    public function redeem(Request $request, int $id): JsonResponse
    {
        $reward = Reward::find($id);

        if (!$reward) {
            return response()->json(['message' => 'المكافأة غير موجودة.'], 404);
        }

        $redemption = $this->pointsService->redeemReward($request->user(), $reward);

        if (!$redemption) {
            return response()->json([
                'message' => 'تعذر الاستبدال. تحقق من رصيد النقاط أو توفر المكافأة.',
            ], 422);
        }

        return response()->json([
            'message' => 'تم استبدال المكافأة بنجاح.',
            'redemption' => [
                'id' => $redemption->id,
                'points_spent' => $redemption->points_spent,
                'code' => $redemption->code,
                'status' => $redemption->status,
                'status_label' => $redemption->status_label,
                'expires_at' => $redemption->expires_at?->toIso8601String(),
            ],
            'new_available_points' => $request->user()->fresh()->available_points,
        ]);
    }
}
