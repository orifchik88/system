<?php

namespace App\Console\Commands;

use App\Helpers\ClaimStatuses;
use App\Models\Article;
use App\Models\DxaResponse;
use App\Models\Response;
use App\Services\DxaBuildingResponseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateResponseCommand extends Command
{

    protected $signature = 'module:create-building-response';


    protected $description = 'Command description';

    public function __construct(
        protected DxaBuildingResponseService $service
    )
    {
        parent::__construct();
    }

    public function handle()
    {

        $data = Response::query()->where('status', ClaimStatuses::RESPONSE_NEW)
            ->where('module', 1)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->take(5)
            ->get();
        foreach ($data as $item) {
            if (!DxaResponse::query()->where('task_id', $item->task_id)->exists()) {
                $item->update(['status' => ClaimStatuses::RESPONSE_PROCESSING]);
                try {
                    DB::transaction(function () use ($item) {
                        $taskId = $item->task_id;
                        $response = $this->service->fetchTaskData($taskId);
                        $data = $this->service->parseResponse($response);
                        $userType = $this->service->determineUserType($data['user_type']['real_value']);
                        $dxa = $this->service->saveDxaResponse($taskId, $data, $userType, $response->json());
                        $this->service->sendMyGov($dxa);
                        $this->service->saveExpertise($dxa);

                        $item->update(['status' => ClaimStatuses::RESPONSE_WATCHED]);
                    });
                } catch (\Exception $exception) {
                    $item->update(['status' => ClaimStatuses::RESPONSE_ERRORED]);
                    Log::error('Xatolik binoda: task_id= '.$item->task_id.'   '.$exception->getMessage());
                }
            } else {
                $item->update(['status' => ClaimStatuses::RESPONSE_WATCHED]);
            }

            sleep(15);
        }
    }
}
