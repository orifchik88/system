<?php

namespace App\Console\Commands;

use App\Helpers\ClaimStatuses;
use App\Models\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class HandleResponseCommand extends Command
{

    protected $signature = 'module:handle-response';


    protected $description = 'Command description';


    public function handle()
    {
        $data = Response::query()
            ->where('status', ClaimStatuses::RESPONSE_ERRORED)
            ->whereNull('module')
            ->take(10)
            ->get();

        foreach ($data as $item) {
            try {
                $this->call('app:network', ['task_id' => $item->task_id]);
                $this->info('app:network successfully executed.');
                $item->update(['status' => 2]);
            } catch (\Exception $e) {
                $this->error('app:network failed: ' . $e->getMessage());
                try {
                    $this->call('app:response', ['task_id' => $item->task_id]);
                    $this->info('app:response successfully executed.');
                    $item->update(['status' => 2]);
                } catch (\Exception $e) {
                    $item->update([
                        'status' =>5
                    ]);
                    Log::info('Xatolik: '. $item->task_id. ' ' .$e->getMessage());
                    $this->error('module:create-building-response failed: ' . $e->getMessage());
                }
            }
        }
    }
}
