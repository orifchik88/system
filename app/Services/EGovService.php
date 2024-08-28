<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class EGovService
{
    private string $tokenUrl = 'https://iskm.egov.uz:9444/oauth2/token';
    private string $apiUrl = 'https://apimgw.egov.uz:8243/gcp/docrest/v1';
    private string $token = "";

    private Client $client;

    public function __construct()
    {
        $headers = [
            'Authorization' => 'Basic SXVnQ2h4XzFabkxsQWhkMEp4OWVtTjZqV3AwYToxUzlrWGxLQzBhWnd3bHNzb28xSzJmM1NRN3dh'
        ];

        $this->client = new Client(['headers' => $headers]);
        $this->token = $this->getToken();
    }

    private function getToken()
    {
        try {
            if (Cache::has('egov_token')) {
                $token = Cache::get('egov_token');
            } else {
                $resClient = $this->client->post($this->tokenUrl,
                    [
                        'form_params' => [
                            'grant_type' => 'password',
                            'username' => 'qv-user',
                            'password' => '8F5zl2w68GU1itlyGF0w',
                        ]
                    ]
                );

                $token = json_decode($resClient->getBody(), true)["access_token"];
                $tokenExpireTime = now()->addMinutes(30);

                Cache::put('egov_token', $token, $tokenExpireTime);
            }

        } catch (BadResponseException $ex) {
            Cache::forget('egov_token');
            return $ex->getResponse()->getBody()->getContents();
        }

        dd($token);
        return $token;
    }

    public function getInfo(string $pinfl)
    {
        $headers = [
            'Authorization' => "Bearer $this->token"
        ];

        $this->client = new Client(['headers' => $headers]);

        try {
            $resClient = $this->client->post($this->apiUrl,
                [
                    'json' => [
                        'sender_pinfl' => $pinfl,
                        'is_photo' => 'Y',
                        'Sender' => 'P',
                        'langId' => 1,
                        'is_consent' => 'Y',
                        'transaction_id' => 123
                    ]
                ]
            );

            $response = $resClient->getBody();
            $statusCode = $resClient->getStatusCode();

        } catch (BadResponseException $ex) {
            $response = $ex->getResponse()->getBody()->getContents();
            $statusCode = $ex->getResponse()->getStatusCode();
        }

        return [$response, $statusCode];
    }

}
