<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sms_provider' => [
        'url' => env('SMS_PROVIDER_URL'),
        'nickname' => env('SMS_PROVIDER_NICKNAME'),
        'login' => env('SMS_PROVIDER_LOGIN'),
        'password' => env('SMS_PROVIDER_PASSWORD'),
    ],

    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID') ?? 'a09289fb-95f4-4e89-a860-b66bcd773242',
        'url' => env('ONESIGNAL_URL') ?? 'https://onesignal.com/api/v1/notifications',
        'token' => env('ONESIGNAL_TOKEN') ?? 'ODFjNmNkOTgtMzI4OS00ZjAxLWI3YmQtNmI2Nzc0M2VlMDVi',
    ],

    'oneId' => [
        'id' => env('ONE_ID_CLIENT_ID'),
        'secret' => env('ONE_ID_CLIENT_SECRET'),
        'redirect' => env('ONE_ID_URL'),
    ],


];
