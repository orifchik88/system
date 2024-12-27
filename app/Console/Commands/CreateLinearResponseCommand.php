<?php

namespace App\Console\Commands;

use App\Helpers\ClaimStatuses;
use App\Models\Article;
use App\Models\DxaResponse;
use App\Models\Response;
use App\Services\DxaLinearResponseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateLinearResponseCommand extends Command
{

    protected $signature = 'module:create-linear-response';


    protected $description = 'Command description';
    public function __construct(
        protected DxaLinearResponseService $service
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $data = Response::query()
            ->where('status', ClaimStatuses::RESPONSE_NEW)
            ->where('module', 3)
            ->orderBy('id')
            ->lockForUpdate()
            ->take(20)
            ->get();

        foreach ($data as $item) {
            if (!DxaResponse::query()->where('task_id', $item->task_id)->exists()) {
                $item->update(['status' => ClaimStatuses::RESPONSE_PROCESSING]);
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

                    Log::info('Xatolik tarmoqda: task_id= '.$item->task_id.'    '. $exception->getMessage());
                    echo $exception->getMessage() . ' ' . $item->task_id;
                    continue;
                }

                sleep(15);
            }else{
                $item->update([
                    'status' => ClaimStatuses::RESPONSE_WATCHED
                ]);
            }

        }
    }
}
