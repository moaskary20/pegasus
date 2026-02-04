<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\User;
use App\Filament\Pages\Conversation as ConversationPage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class Messages extends Page
{
    use WithPagination;
    
    protected static ?string $navigationLabel = 'الرسائل';
    
    protected static ?string $title = 'الرسائل';
    
    protected static ?int $navigationSort = 5;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    
    protected string $view = 'filament.pages.messages';
    
    protected static ?string $slug = 'messages';
    
    public string $filter = 'all'; // all, unread, groups
    public string $search = '';
    public bool $showNewConversationModal = false;
    public string $searchUsers = '';
    public ?int $selectedUserId = null;
    
    public static function getNavigationGroup(): ?string
    {
        return 'التواصل';
    }
    
    public static function getNavigationBadge(): ?string
    {
        $count = auth()->user()->unread_messages_count ?? 0;
        return $count > 0 ? (string) $count : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    
    public function getConversationsProperty()
    {
        $query = Conversation::forUser(auth()->id())
            ->with(['users', 'latestMessage.user', 'participants'])
            ->orderByDesc('last_message_at');
        
        // Apply filters
        if ($this->filter === 'unread') {
            $query->withUnread(auth()->id());
        } elseif ($this->filter === 'groups') {
            $query->whereIn('type', [Conversation::TYPE_GROUP, Conversation::TYPE_COURSE]);
        }
        
        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', "%{$this->search}%")
                  ->orWhereHas('users', fn($u) => $u->where('name', 'LIKE', "%{$this->search}%"));
            });
        }
        
        return $query->paginate(20);
    }
    
    public function getSearchUsersResultsProperty()
    {
        if (strlen($this->searchUsers) < 2) {
            return collect();
        }
        
        return User::where('id', '!=', auth()->id())
            ->search($this->searchUsers)
            ->limit(10)
            ->get();
    }
    
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }
    
    public function openNewConversation(): void
    {
        $this->showNewConversationModal = true;
        $this->searchUsers = '';
        $this->selectedUserId = null;
    }
    
    public function closeNewConversation(): void
    {
        $this->showNewConversationModal = false;
        $this->searchUsers = '';
        $this->selectedUserId = null;
    }
    
    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
    }
    
    public function startConversation(): void
    {
        if (!$this->selectedUserId) {
            return;
        }
        
        $conversation = Conversation::getOrCreatePrivate(auth()->id(), $this->selectedUserId);
        
        $this->closeNewConversation();
        
        $this->redirect(ConversationPage::getUrl(['id' => $conversation->id]));
    }
    
    public function openConversation(int $conversationId): void
    {
        $this->redirect(ConversationPage::getUrl(['id' => $conversationId]));
    }
    
    public function getUnreadCount(Conversation $conversation): int
    {
        return $conversation->getUnreadCountFor(auth()->id());
    }
    
    public function getConversationName(Conversation $conversation): string
    {
        if ($conversation->type === Conversation::TYPE_PRIVATE) {
            $other = $conversation->getOtherParticipant(auth()->id());
            return $other?->name ?? 'محادثة';
        }
        
        return $conversation->name ?? 'مجموعة';
    }
    
    public function getConversationAvatar(Conversation $conversation): ?string
    {
        if ($conversation->type === Conversation::TYPE_PRIVATE) {
            $other = $conversation->getOtherParticipant(auth()->id());
            return $other?->avatar;
        }
        
        return null;
    }
}
