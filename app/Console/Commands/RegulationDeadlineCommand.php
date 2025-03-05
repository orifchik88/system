<?php

namespace App\Console\Commands;

use App\Enums\LawyerStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Regulation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegulationDeadlineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'regulation:status-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Regulation::query()
                ->whereIn('regulation_status_id', [RegulationStatusEnum::PROVIDE_REMEDY, RegulationStatusEnum::ATTACH_DEED])
                ->where('deadline', '<=', Carbon::now())
                ->whereNotNull('deadline')
                ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
                ->chunk(100, function ($regulations) {
                    foreach ($regulations as $regulation) {
                        $regulation->update([
                            'regulation_status_id' => RegulationStatusEnum::IN_LAWYER,
                            'lawyer_status_id' => LawyerStatusEnum::NEW,
                            'deadline_rejected' => true,
                        ]);
                    }
                });
        }catch (\Exception $exception){
            Log::info('xotolik regulation deadline'.$exception->getMessage());
        }


    }
}
