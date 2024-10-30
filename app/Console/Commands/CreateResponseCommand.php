<?php

namespace App\Console\Commands;

use App\Helpers\ClaimStatuses;
use App\Models\Response;
use App\Services\DxaBuildingResponseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            ->where('module', '=', 1)
            ->orderBy('id', 'asc')
            ->take(10)
            ->get();
        foreach ($data as $item) {
            DB::beginTransaction();
            try {
                $taskId = $item->task_id;
                $response = $this->service->fetchTaskData($taskId);
                $data = $this->service->parseResponse($response);
                $userType = $this->service->determineUserType($data['user_type']['real_value']);
                $dxa = $this->service->saveDxaResponse($taskId, $data, $userType, $response->json());
                $this->service->sendMyGov($dxa);
                $this->service->saveExpertise($dxa);

                $item->update([
                    'status' => ClaimStatuses::RESPONSE_WATCHED
                ]);

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                $item->update([
                    'status' => ClaimStatuses::RESPONSE_ERRORED
                ]);
                echo $exception->getMessage() . ' ' . $exception->getLine();
            }
            sleep(5);
        }
    }
}
