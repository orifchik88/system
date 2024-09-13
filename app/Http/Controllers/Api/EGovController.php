<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PinflRequest;
use App\Services\EGovService;
use Illuminate\Http\Request;

class EGovController
{

    public function getPassportInfo()
    {

        dd(env('APP_ENV') === 'development');
    }

}
