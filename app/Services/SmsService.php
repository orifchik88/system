<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function __construct( protected ?int $phone , protected string $message){}

    public function sendSms(): void
    {
        $phone = str_replace('+', '', $this->phone);
        $data = [
            'login' => config('services.sms_provider.login'),
            'password' => config('services.sms_provider.password'),
//            'nickname' => config('services.sms_provider.nickname'),
            'data' => json_encode([[
                'phone' => $phone,
                'text' => $this->message
            ]])
        ];

        $url = config('services.sms_provider.url');
        Http::post($url, $data);
    }
}
