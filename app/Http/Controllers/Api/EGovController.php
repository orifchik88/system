<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PinflRequest;
use App\Services\EGovService;
use Illuminate\Http\Request;

class EGovController
{
    private EGovService $govService;

    public function __construct(EGovService $govService)
    {
        $this->govService = $govService;
    }

    public function getPassportInfo(PinflRequest $request)
    {
        return $this->govService->getInfo($request->get('pinfl'), '31102975540014', $request->get('birth_date'));
    }

}
