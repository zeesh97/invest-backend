<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OldBackupsDeletedNotification extends Notification
{
    use Queueable;

    protected int $deletedCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $deletedCount)
    {
        $this->deletedCount = $deletedCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Add more channels if needed
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Old Backups Deleted')
                    ->line("{$this->deletedCount} backups older than 7 days have been successfully deleted.")
                    ->line('Thank you for keeping your storage optimized!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'deleted_count' => $this->deletedCount,
        ];
    }
}
