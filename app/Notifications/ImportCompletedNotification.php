<?php

namespace App\Notifications;

use App\Enums\Resources;
use App\Traits\HasRateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use HasRateLimiter, Queueable;

    public function __construct(
        protected Resources $resourceName,
        protected int $count
    ) {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('ui.import_completed', ['resource' => __($this->resourceName->value)]))
            ->greeting(__('ui.hello', ['name' => $notifiable->name]))
            ->line(__('ui.import_completed_message', ['count' => $this->count, 'resource' => __($this->resourceName->value)]))
            ->line(__('ui.thank_you_for_using_our_application'))
            ->action(__('ui.view_dashboard'), url('/'.$this->resourceName->getPluralName()));

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
}
