<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get paginated notifications for the authenticated user (session or Sanctum)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $query = $user->notifications();
        
        // Filter by read status
        if ($request->has('unread') && $request->boolean('unread')) {
            $query = $user->unreadNotifications();
        }
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('data->type', $request->type);
        }
        
        $notifications = $query
            ->latest()
            ->paginate($request->input('per_page', 15));
        
        return response()->json([
            'notifications' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }
    
    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        
        return response()->json([
            'count' => $user->unreadNotifications()->count(),
        ]);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $user->unreadNotifications->markAsRead();
        
        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
    
    /**
     * Delete a notification
     */
    public function destroy(string $id): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
    
    /**
     * Delete all read notifications
     */
    public function destroyRead(): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $user->readNotifications()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعارات المقروءة',
        ]);
    }
}
