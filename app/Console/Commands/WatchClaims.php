<?php

namespace App\Console\Commands;

use App\Enums\LogType;
use App\Helpers\ClaimStatuses;
use App\Services\ClaimService;
use App\Services\HistoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;



class  WatchClaims extends Command
{
    private ClaimService $claimService;
    private HistoryService $historyService;

    protected $name = "module2:watch_tasks";

    protected $description = "Приемка (ввод в эксплуатацию) объектов с завершенными строительно-монтажными работами и оформление кадастровых документов";

    public function __construct(
        ClaimService $claimService
    )
    {
        parent::__construct();

        $this->claimService = $claimService;
        $this->historyService = new HistoryService('claim_histories');
    }

    public function handle(): void
    {
        $responses = $this->claimService->getActiveResponses();

        foreach ($responses as $response) {
            DB::beginTransaction();

            try {
                $taskFormGov = $this->claimService->getClaimFromApi($response->task_id);
                if(!$taskFormGov) {
                    $this->claimService->updateResponseStatus(
                        guId: $response->task_id,
                        status: ClaimStatuses::RESPONSE_ERRORED
                    );

                    DB::commit();
                    continue;
                }

                $status = ClaimStatuses::TASK_STATUS_ANOTHER;
                if($taskFormGov->task->current_node == "direction-statement-object")
                    $status = ClaimStatuses::TASK_STATUS_ACCEPTANCE;
                if($taskFormGov->task->current_node == "answer-other-institutions")
                    $status = ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION;
                if($taskFormGov->task->current_node == "conclusion-minstroy")
                    $status = ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG;
                if($taskFormGov->task->current_node == "inactive" && $taskFormGov->task->status == "processed")
                    $status = ClaimStatuses::TASK_STATUS_CONFIRMED;
                if($taskFormGov->task->current_node == "inactive" && $taskFormGov->task->status == "rejected")
                    $status = ClaimStatuses::TASK_STATUS_REJECTED;
                if($taskFormGov->task->current_node == "inactive" && $taskFormGov->task->status == "not_active")
                    $status = ClaimStatuses::TASK_STATUS_CANCELLED;

                $this->claimService->updateResponseStatus(
                    guId: $response->task_id,
                    status: ClaimStatuses::RESPONSE_WATCHED
                );

                if($status == ClaimStatuses::TASK_STATUS_ACCEPTANCE || $status == ClaimStatuses::TASK_STATUS_ANOTHER) {
                    $this->historyService->createHistory(
                        guId: $response->task_id,
                        status: $status,
                        type: LogType::TASK_HISTORY,
                        date: null
                    );
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();

                $this->output->writeln($e->getMessage());
                $this->output->writeln($e->getTraceAsString());

                $this->claimService->updateResponseStatus(
                    guId: $response->task_id,
                    status: ClaimStatuses::RESPONSE_ERRORED
                );
            }

            sleep(2);
        }
    }
}
