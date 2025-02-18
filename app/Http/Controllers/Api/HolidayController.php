<?php

namespace App\Http\Controllers\Api;



use App\Http\Requests\HolidayCreateRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HolidayController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        try {
            $holidays = Holiday::query()->select('id', 'name', 'day')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($holiday) {
                    return $holiday->created_at->format('Y');
                });

            $holidays->toArray();

            return $this->sendSuccess($holidays, 'Holidays retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError('Xatolik', $exception->getMessage());
        }
    }

    public function create(HolidayCreateRequest $request): JsonResponse
    {
        try {
            $holiday = Holiday::query()->create($request->validated());
            return $this->sendSuccess(new HolidayResource($holiday), 'Holiday created successfully.');
        }catch (\Exception $exception){
            return $this->sendError('Xatolik', $exception->getMessage());
        }
    }
}
