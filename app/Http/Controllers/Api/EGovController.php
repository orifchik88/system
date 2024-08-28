<?php

namespace App\Http\Controllers\Api;

use App\Services\EGovService;
use Illuminate\Http\Request;

class EGovController
{
    private EGovService $govService;
    public function __construct(EGovService $govService)
    {
        $this->govService = $govService;
    }

    public function getPassportInfo(Request $request)
    {
        return $this->govService->getInfo($request);
    }

}
