<?php

namespace App\Notifications;

use App\Traits\HasRateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as Notification;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\HtmlString;

class BroadcastMessageNotification extends Notification implements ShouldQueue
{
    use Queueable, HasRateLimiter;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected array $context, protected $sendAlsoMail)
    {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if($this->sendAlsoMail) {
            return ['mail', 'database'];
        }

        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->context['title'] ?? 'Notification')
            ->greeting(__('ui.notification_greeting', ['name' => $notifiable->first_name]))
            ->line(new HtmlString($this->context['description']) ?? 'No description provided.')
            ->line(__('ui.thank_you_for_using_our_application'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'context' => $this->context,
        ];
    }
}
