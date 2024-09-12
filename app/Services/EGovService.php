<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class EGovService
{

    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }



}
