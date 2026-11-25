<?php

namespace App\Notifications;

use App\Models\Leads\Lead;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAccessGrantedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Lead $lead,
        public User $grantedBy,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Se te ha dado acceso al lead \"{$this->lead->name_company}\"",
            'lead_id' => $this->lead->id,
            'granted_by' => $this->grantedBy->name,
            'url' => route('leads.show', $this->lead),
        ];
    }
}
