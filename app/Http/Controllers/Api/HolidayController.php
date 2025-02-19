<?php

namespace App\Http\Controllers\Api;



use App\Http\Requests\HolidayCreateRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use Carbon\Carbon;
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

            $holidays = Holiday::query()
                ->select('id', 'name', 'day', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($holiday) {
                    return Carbon::parse($holiday->day)->format('Y');
                });

            $holidays = $holidays->toArray();


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
