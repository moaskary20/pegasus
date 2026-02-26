<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    /**
     * Get unread messages count for header badge
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => $user->unread_messages_count ?? 0,
        ]);
    }

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

    /**
     * عرض محادثة مع رسائلها
     * GET /api/messages/conversations/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $conversation = Conversation::with(['users', 'participants'])->find($id);
        if (!$conversation || !$conversation->hasParticipant($user->id)) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        $this->markAsRead($conversation);

        $displayName = $conversation->name;
        $otherUser = null;
        if ($conversation->type === Conversation::TYPE_PRIVATE) {
            $other = $conversation->getOtherParticipant($user->id);
            $displayName = $other?->name ?? 'مستخدم';
            $otherUser = $other ? [
                'id' => $other->id,
                'name' => $other->name,
                'avatar' => $other->avatar ? asset('storage/' . ltrim($other->avatar, '/')) : null,
            ] : null;
        }

        $messages = $conversation->messages()
            ->with(['user', 'attachments'])
            ->orderBy('created_at')
            ->get();

        $messagesList = $messages->map(fn (Message $m) => [
            'id' => $m->id,
            'user_id' => $m->user_id,
            'body' => $m->body ?? '',
            'type' => $m->type,
            'created_at' => $m->created_at->toIso8601String(),
            'is_mine' => $m->user_id === $user->id,
            'sender_name' => $m->user?->name ?? '',
            'attachments' => $m->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'url' => asset('storage/' . ltrim($a->file_path, '/')),
                'file_type' => $a->file_type,
            ])->all(),
        ])->all();

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'name' => $displayName,
                'type' => $conversation->type,
                'other_user' => $otherUser,
            ],
            'messages' => $messagesList,
        ]);
    }

    /**
     * إرسال رسالة
     * POST /api/messages/conversations/{id}/send
     * body: body (نص الرسالة، مطلوب إذا لم يُرفق ملف)
     */
    public function send(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasParticipant($user->id)) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        $attachment = $request->file('attachment');

        if ($body === '' && !$attachment) {
            return response()->json(['message' => 'أدخل رسالة أو مرفق'], 422);
        }

        $type = Message::TYPE_TEXT;
        if ($attachment) {
            $type = str_starts_with($attachment->getMimeType(), 'image/')
                ? Message::TYPE_IMAGE
                : Message::TYPE_FILE;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'body' => $body ?: null,
            'type' => $type,
        ]);

        if ($attachment) {
            $path = $attachment->store('message-attachments', 'public');
            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name' => $attachment->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $attachment->getMimeType(),
                'file_size' => $attachment->getSize(),
            ]);
        }

        $conversation->update(['last_message_at' => now()]);

        foreach ($conversation->participants()->where('user_id', '!=', $user->id)->where('is_muted', false)->with('user')->get() as $p) {
            if ($p->user) {
                $p->user->notify(new NewMessageNotification($message));
            }
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'body' => $message->body ?? '',
                'type' => $message->type,
                'created_at' => $message->created_at->toIso8601String(),
                'is_mine' => true,
            ],
        ]);
    }

    /**
     * بدء محادثة أو الحصول عليها
     * POST /api/messages/start
     * body: user_id
     */
    public function start(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userId = (int) $request->input('user_id');
        if (!$userId || $userId === $user->id) {
            return response()->json(['message' => 'user_id مطلوب ويجب أن يكون مختلفاً عنك'], 422);
        }

        $other = User::find($userId);
        if (!$other) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $conversation = Conversation::getOrCreatePrivate($user->id, $userId);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $other->name,
                'other_user' => [
                    'id' => $other->id,
                    'name' => $other->name,
                    'avatar' => $other->avatar ? asset('storage/' . ltrim($other->avatar, '/')) : null,
                ],
            ],
        ]);
    }

    /**
     * البحث عن مستخدمين لبدء محادثة
     * GET /api/messages/users?q=...
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        $term = '%' . $q . '%';
        $users = User::where('id', '!=', $user->id)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term)
                    ->orWhere('phone', 'LIKE', $term);
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email', 'avatar']);

        $list = $users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'avatar' => $u->avatar ? asset('storage/' . ltrim($u->avatar, '/')) : null,
        ])->all();

        return response()->json(['users' => $list]);
    }

    protected function markAsRead(Conversation $conversation): void
    {
        $participant = $conversation->participants()
            ->where('user_id', Auth::id())
            ->first();

        if ($participant) {
            $participant->markAsRead();
        }
    }
}
