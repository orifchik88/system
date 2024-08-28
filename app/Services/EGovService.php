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
        $this->client = new Client(['headers' => ['Authorization' => 'Basic ' . base64_encode(env('BANK_USERNAME' . ':' . env('BANK_PASSWORD')))]]);
    }

    public function getInfo(string $pinfl, string $sender_pinfl, string $birth_date)
    {
        try {
            $resClient = $this->client->post($this->apiUrl . '?pinfl=' . $pinfl . '&sender_pinfl=' . $sender_pinfl . '&birth_date=' . $birth_date);

            $response = $resClient->getBody();
            $statusCode = $resClient->getStatusCode();

        } catch (BadResponseException $ex) {
            $response = $ex->getResponse()->getBody()->getContents();
            $statusCode = $ex->getResponse()->getStatusCode();
        }

        return [$response, $statusCode];
    }

}
