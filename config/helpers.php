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
if (!function_exists('getRegionName')) {
    function getRegionName(?int $regionId){
        $array = [
            1 => 'города Ташкент',
            2 => 'Ташкентской области',
            3 => 'Сырдарьинской области',
            4 => 'Джизакской области',
            5 => 'Самаркандской области',
            6 => 'Ферганской области',
            7 => 'Наманганской области',
            8 => 'Андижанской области',
            9 => 'Кашкадарьинской области',
            10 => 'Сурхандарьинской области',
            11 => 'Бухарской области',
            12 => 'Навоийской области',
            13 => 'Хорезмской области',
            14 => 'Республики Каракалпакстан',
        ];


        return $array[$regionId] ?? null;
    }
}





