<?php

namespace App\Helpers;

use App\Enums\NotificationTypeEnum;
use App\Models\NotificationLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class OneSignal
{
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toOneSignal')) {
            $data = [
                'app_id' => config('services.onesignal.app_id'),
                ...$notification->toOneSignal($notifiable)
            ];

            Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => config('services.onesignal.token'),
            ])->post(config('services.onesignal.url'), [
                'app_id' => $data['app_id'],
                'include_external_user_ids' => [$data['notification_app_id']],
                'data' => $data['additionalInfo'],
                'contents' => [
                    'en' => $data['message'],
                ],
                'big_picture' => $data['url'],
                'headings' => [
                    'en' => $data['title'],
                ]
            ]);

            $this->saveNotification($data, $notifiable);
        }
    }

    private function saveNotification($data, $notifiable)
    {
        NotificationLog::query()->create([
            'type' => NotificationTypeEnum::DEVICE,
            'user_id' => $notifiable->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'image_url' => $data['url'],
            'additional_info' => json_encode($data['additionalInfo']),
            'sent_at' => now(),
        ]);
    }
}
