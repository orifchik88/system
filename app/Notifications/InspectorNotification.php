<?php

namespace App\Notifications;

use App\Helpers\OneSignal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class InspectorNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $title,
        protected string $message,
        protected ?string $url = null,
        protected ?array  $additionalInfo = null
    )
    {

    }

    public function via(object $notifiable): array
    {
        return [OneSignal::class];
    }

    public function toOneSignal($notifiable): array
    {
        return [
            'notification_app_id' => $notifiable->notification_app_id,
            'additionalInfo' => $this->additionalInfo,
            'message' => $this->message,
            'url' => $this->url,
            'title' => $this->title,
        ];
    }

}
