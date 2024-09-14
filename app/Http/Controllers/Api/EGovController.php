<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PinflRequest;
use App\Services\EGovService;
use Illuminate\Http\Request;

class EGovController
{

    public function getPassportInfo()
    {
        $data = getData(config('app.gasn.sphere'));
        dd($data['data']['data']);


//        dd(env('APP_ENV') === 'development');
    }

}
