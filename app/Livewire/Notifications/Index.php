<?php

namespace App\Livewire\Notifications;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Notificaciones')]
class Index extends Component
{
    use WithPagination;

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public string $filterRead = 'all'; // 'all' | 'unread' | 'read'

    public function getNotificationsProperty(): LengthAwarePaginator
    {
        $query = auth()->user()
            ->notifications()
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        if ($this->filterRead === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filterRead === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->paginate(15);
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function markRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->find($id)
            ?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification(string $id): void
    {
        auth()->user()
            ->notifications()
            ->find($id)
            ?->delete();
    }

    public function updatedFilterRead(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.notifications.index', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
