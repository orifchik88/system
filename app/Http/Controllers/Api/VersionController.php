<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VersionRequest;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function index($type): JsonResponse
    {
        $version = AppVersion::query()->where('type', $type)->first();
        return response()->json([
            'id' => $version->id,
            'version' => $version->version,
            'app_link' => $version->app_link,
            'type' => $version->type,
            'message' => $version->message,
        ]);
    }

    public function update(VersionRequest $request)
    {
        $version = AppVersion::query()->where('type', $request->type)->first();

        if (!$version) {
            return response()->json(['message' => 'App version not found'], 404);
        }

        $version->update($request->validated());

        return response()->json([
            'id' => $version->id,
            'version' => $version->version,
            'app_link' => $version->app_link,
            'type' => $version->type,
            'message' => $version->message,
        ]);
    }
}
