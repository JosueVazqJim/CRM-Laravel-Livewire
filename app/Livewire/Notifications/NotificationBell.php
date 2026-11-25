<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public array $notifications = [];

    public int $unreadCount = 0;

    public bool $isOpen = false;

    public int $notificationsPerPage = 10;

    public int $userId;

    public function mount(): void
    {
        $this->userId = auth()->id();
        // Cargar notificaciones persistidas (base de datos) al montar
        $this->loadNotifications();
    }

    private function loadNotifications(): void
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    public function loadMore(): void
    {
        $this->notificationsPerPage += 10;
        $this->loadNotifications();
    }

    // Livewire 4: escucha evento JS disparado por Echo
    #[On('echo-private:user.{userId},.lead.access.granted')]
    public function onLeadAccessGranted(array $event): void
    {
        $this->loadNotifications();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $event['message'],
        ]);
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function markRead(string $notificationId): void
    {
        auth()->user()
            ->notifications()
            ->find($notificationId)
            ?->markAsRead();

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications.notification-bell');
    }
}
