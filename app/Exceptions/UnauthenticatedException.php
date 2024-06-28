<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UnauthenticatedException extends Exception
{
    public function report()
    {

    }

    public function render(): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $this->getMessage(),
            'code'=> $this->code,
        ], $this->code);
    }
}
