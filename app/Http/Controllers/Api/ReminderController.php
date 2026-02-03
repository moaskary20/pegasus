<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        protected ReminderService $reminderService
    ) {}
    
    /**
     * Get reminders for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $reminders = $this->reminderService->generateReminders($user);
        
        // Filter by type if provided
        if ($type = $request->input('type')) {
            $reminders = $reminders->where('type', $type);
        }
        
        return response()->json([
            'reminders' => $reminders->values(),
            'meta' => [
                'total' => $reminders->count(),
            ],
        ]);
    }
    
    /**
     * Get reminder counts
     */
    public function counts(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $counts = $this->reminderService->getReminderCounts($user);
        
        return response()->json($counts);
    }
    
    /**
     * Dismiss a reminder
     */
    public function dismiss(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $type = $request->input('type');
        $remindableId = $request->input('remindable_id');
        
        if (!$type) {
            return response()->json(['error' => 'Type is required'], 400);
        }
        
        $this->reminderService->dismissReminder($user, $type, $remindableId);
        
        return response()->json(['success' => true]);
    }
}
