<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    /**
     * Stream notifications using Server-Sent Events
     */
    public function stream(): StreamedResponse
    {
        return response()->stream(function () {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            $lastCheck = now();
            $retryCount = 0;
            $maxRetries = 60; // Max 5 minutes (60 * 5 seconds)
            
            while ($retryCount < $maxRetries) {
                if (!Auth::check()) {
                    echo "event: error\n";
                    echo "data: " . json_encode(['error' => 'Unauthenticated']) . "\n\n";
                    break;
                }
                
                $user = Auth::user();
                
                // Get unread notifications
                $unreadCount = $user->unreadNotifications()->count();
                $latestNotifications = $user->unreadNotifications()
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function ($notification) {
                        return [
                            'id' => $notification->id,
                            'type' => $notification->data['type'] ?? 'general',
                            'title' => $notification->data['title'] ?? 'إشعار جديد',
                            'message' => $notification->data['message'] ?? '',
                            'created_at' => $notification->created_at->diffForHumans(),
                            'created_at_timestamp' => $notification->created_at->timestamp,
                        ];
                    });
                
                // Send the data
                echo "event: notification\n";
                echo "data: " . json_encode([
                    'count' => $unreadCount,
                    'notifications' => $latestNotifications,
                    'timestamp' => now()->timestamp,
                ]) . "\n\n";
                
                // Flush output
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
                
                $retryCount++;
                sleep(5); // Wait 5 seconds before next check
            }
            
            // Send close event
            echo "event: close\n";
            echo "data: " . json_encode(['message' => 'Stream ended']) . "\n\n";
            
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }
    
    /**
     * Get current notification count (for polling fallback)
     */
    public function count(): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }
        
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
