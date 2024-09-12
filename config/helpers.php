<?php
use GuzzleHttp\Client;
if (!function_exists('price_supervision')) {
    function price_supervision($price)
    {
        $rate = config("app.rate_supervision");
        $amount = $price * ($rate / 100);

        return number_format($amount, 2, '.', '');
    }
}

if (!function_exists('pagination')) {
    function pagination(object $model)
    {
        if ($model)
            $data = [
                'lastPage' => $model->lastPage(),
                'total' => $model->total(),
                'perPage' => $model->perPage(),
                'currentPage' => $model->currentPage(),
            ];
        else
            $data = [
                'lastPage' => 0,
                'total' => 0,
                'perPage' => 0,
                'currentPage' => 0,
            ];
        return $data;
    }
}

if (!function_exists('getData')) {
    function getData(?string $baseUrl, ?string $param = null){
        $client = new Client();
        $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');
        $url = $param ? $baseUrl.'='.$param : $baseUrl;


        $resClient = $client->post($url,
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                ]
            ]);
        $response = json_decode($resClient->getBody(), true);
        return $response['result'];
    }
}

if (!function_exists('egovAction')) {
    function egovAction(?string $baseUrl, ?string $param = null){
        $client = new Client();
        $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');
        $url = $param ? $baseUrl.'='.$param : $baseUrl;


        $resClient = $client->post($url,
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                ]
            ]);
        $response = json_decode($resClient->getBody(), true);
        return $response['result'];
    }
}


