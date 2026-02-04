<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessagesController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (!auth()->check()) {
            session(['url.intended' => route('site.messages')]);
            return redirect(route('site.auth'));
        }

        $filter = $request->query('filter', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = Conversation::forUser(auth()->id())
            ->with(['users', 'latestMessage.user', 'participants'])
            ->orderByDesc('last_message_at');

        if ($filter === 'unread') {
            $query->withUnread(auth()->id());
        } elseif ($filter === 'groups') {
            $query->whereIn('type', [Conversation::TYPE_GROUP, Conversation::TYPE_COURSE]);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('users', fn ($u) => $u->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $conversations = $query->paginate(20)->withQueryString();

        return view('pages.messages.index', [
            'conversations' => $conversations,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function show(int $id): View|RedirectResponse
    {
        if (!auth()->check()) {
            session(['url.intended' => route('site.messages.show', $id)]);
            return redirect(route('site.auth'));
        }

        $conversation = Conversation::with(['users', 'participants'])->findOrFail($id);

        if (!$conversation->hasParticipant(auth()->id())) {
            abort(403);
        }

        $this->markAsRead($conversation);

        $messages = $conversation->messages()
            ->with(['user', 'attachments'])
            ->orderBy('created_at')
            ->get();

        return view('pages.messages.show', [
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, int $id): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect(route('site.auth'));
        }

        $conversation = Conversation::findOrFail($id);

        if (!$conversation->hasParticipant(auth()->id())) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        $attachment = $request->file('attachment');

        if ($body === '' && !$attachment) {
            return redirect()->route('site.messages.show', $id)
                ->with('notice', ['type' => 'error', 'message' => 'أدخل رسالة أو مرفق.']);
        }

        $type = Message::TYPE_TEXT;
        if ($attachment) {
            $type = str_starts_with($attachment->getMimeType(), 'image/')
                ? Message::TYPE_IMAGE
                : Message::TYPE_FILE;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $body,
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

        foreach ($conversation->participants()->where('user_id', '!=', auth()->id())->where('is_muted', false)->with('user')->get() as $p) {
            if ($p->user) {
                $p->user->notify(new NewMessageNotification($message));
            }
        }

        return redirect()->route('site.messages.show', $id)
            ->withFragment('messages-bottom');
    }

    public function newConversation(Request $request): View|RedirectResponse
    {
        if (!auth()->check()) {
            session(['url.intended' => route('site.messages.new')]);
            return redirect(route('site.auth'));
        }

        $search = trim((string) $request->query('q', ''));
        $users = collect();

        if (strlen($search) >= 2) {
            $term = '%' . $search . '%';
            $users = User::where('id', '!=', auth()->id())
                ->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', $term)
                        ->orWhere('email', 'LIKE', $term)
                        ->orWhere('phone', 'LIKE', $term);
                })
                ->orderBy('name')
                ->limit(15)
                ->get();
        }

        return view('pages.messages.new', [
            'search' => $search,
            'users' => $users,
        ]);
    }

    public function startConversation(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect(route('site.auth'));
        }

        $userId = (int) $request->input('user_id');
        if (!$userId) {
            return redirect()->route('site.messages')->with('notice', ['type' => 'error', 'message' => 'اختر مستخدماً.']);
        }

        $conversation = Conversation::getOrCreatePrivate(auth()->id(), $userId);

        return redirect()->route('site.messages.show', $conversation->id);
    }

    protected function markAsRead(Conversation $conversation): void
    {
        $participant = $conversation->participants()
            ->where('user_id', auth()->id())
            ->first();

        if ($participant) {
            $participant->markAsRead();
        }
    }

    public static function getConversationName(Conversation $conversation): string
    {
        if ($conversation->type === Conversation::TYPE_PRIVATE) {
            $other = $conversation->getOtherParticipant(auth()->id());
            return $other?->name ?? 'محادثة';
        }
        return $conversation->name ?? 'مجموعة';
    }

    public static function getConversationAvatar(Conversation $conversation): ?string
    {
        if ($conversation->type === Conversation::TYPE_PRIVATE) {
            $other = $conversation->getOtherParticipant(auth()->id());
            return $other?->avatar;
        }
        return null;
    }
}
