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
                if (!$taskFormGov) {
                    $this->claimService->updateResponseStatus(
                        guId: $response->task_id,
                        status: ClaimStatuses::RESPONSE_ERRORED
                    );

                    DB::commit();
                    continue;
                }

                $this->claimService->updateResponseStatus(
                    guId: $response->task_id,
                    status: ClaimStatuses::RESPONSE_WATCHED
                );

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
