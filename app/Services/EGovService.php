<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class EGovService
{
    private string $apiUrl = 'https://api.shaffofqurilish.uz/api/v1/get-egov-token';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getInfo(string $pinfl, string $sender_pinfl, string $birth_date)
    {
        $apiCredentials = env('BANK_USERNAME') . ':' . env('BANK_PASSWORD');
        try {
            $resClient = $this->client->post($this->apiUrl . '?pinfl=' . $pinfl . '&sender_pinfl=' . $sender_pinfl . '&birth_date=' . $birth_date,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);

            $response = json_decode($resClient->getBody(), true);
        } catch (BadResponseException $ex) {
            $response = $ex->getResponse()->getBody()->getContents();
        }

        return $response;
    }

}
