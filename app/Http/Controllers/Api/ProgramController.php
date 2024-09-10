<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends BaseController
{
    public function programs(): JsonResponse
    {
        try {
           $programs = Program::query()->paginate(request('per_page', 10));
           return $this->sendSuccess(ProgramResource::collection($programs), 'All programs retrieved successfully.', pagination($programs));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
