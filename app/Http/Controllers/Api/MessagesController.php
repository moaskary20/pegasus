<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    /**
     * Get recent conversations for the header dropdown
     */
    public function recent(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['conversations' => [], 'unread_count' => 0]);
        }

        $conversations = Conversation::forUser($user->id)
            ->with(['users', 'latestMessage.user', 'participants'])
            ->orderByDesc('last_message_at')
            ->limit((int) $request->input('limit', 8))
            ->get();

        $unreadCount = 0;
        $items = [];

        foreach ($conversations as $conv) {
            $unread = $conv->getUnreadCountFor($user->id);
            $unreadCount += $unread;

            $displayName = $conv->name;
            if ($conv->type === Conversation::TYPE_PRIVATE) {
                $other = $conv->getOtherParticipant($user->id);
                $displayName = $other?->name ?? 'مستخدم';
            }

            $lastMsg = $conv->latestMessage;
            $rawBody = $lastMsg ? (string) ($lastMsg->body ?? '') : '';
            $lastPreview = $lastMsg ? \Illuminate\Support\Str::limit(strip_tags($rawBody), 50) : '—';
            $lastFrom = $lastMsg && $lastMsg->user_id === $user->id ? 'أنت: ' : '';

            $items[] = [
                'id' => $conv->id,
                'name' => $displayName,
                'last_preview' => $lastFrom . $lastPreview,
                'last_at' => $lastMsg?->created_at?->diffForHumans(),
                'unread' => $unread,
                'url' => route('site.messages.show', $conv->id),
            ];
        }

        return response()->json([
            'conversations' => $items,
            'unread_count' => $user->unread_messages_count ?? 0,
        ]);
    }
}
