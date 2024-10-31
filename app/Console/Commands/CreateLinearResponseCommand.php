<?php

namespace App\Console\Commands;

use App\Helpers\ClaimStatuses;
use App\Models\Article;
use App\Models\Response;
use App\Services\DxaLinearResponseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            ->take(10)
            ->get();

        foreach ($data as $item) {
            $objectExist = Article::query()->where('task_id', $item->task_id)->exists();
            if (!$objectExist) {
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
}
