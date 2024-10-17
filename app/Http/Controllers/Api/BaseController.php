<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected $user;
    protected  $roleId;

    public function __construct()
    {
        $this->user = Auth::guard('api')->user();
        if ($this->user)  $this->roleId = $this->user->getRoleFromToken();

    }

    public function sendSuccess($result, $message, $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'result' => [
                'data' => $result,
            ],
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
            'code' => $code,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
