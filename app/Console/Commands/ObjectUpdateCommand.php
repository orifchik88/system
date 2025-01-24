<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\select;

class ObjectUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:object-update-command';

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
             Article::query()
                ->select('id', 'task_id', 'reestr_number')
                ->whereNotNull('reestr_number')
                ->whereRaw("reestr_number ~ '^[0-9]+$'")
                ->whereRaw("CAST(reestr_number AS BIGINT) > 200000")
                ->whereRaw("CAST(reestr_number AS BIGINT) < 300000")
                ->where('created_at', '>=', '2024-01-01 00:00:00')
                 ->where('funding_source_id', 1)
                ->whereNotNull('old_id')
                ->where('is_change', false)
                ->chunk(5, function ($articles) {
                        foreach ($articles as $article) {
                            $tenderData = getData(config('app.gasn.tender'), $article->reestr_number);
                            if (!$tenderData || !isset($tenderData['data']['result']['data'])) {
                                $article->update(['is_change' => true]);
                                Log::warning('Tender maʼlumotlari topilmadi', ['reestr_number' => $article->reestr_number]);
                            }

                            $article->update([
                                'gnk_id' => $tenderData['data']['result']['data']['gnk_id'],
                                'funding_source_id' => $tenderData['data']['result']['data']['finance_source'] ,
                                'is_change' => true,
                            ]);

                            if ($article->gnk_id) {
                                $monitoringData = getData(config('app.gasn.get_monitoring'), $article->gnk_id);
                                if (!$monitoringData || !isset($monitoringData['data']['result']['data'][0])) {
                                    $article->update(['is_change' => true]);
                                    Log::warning('Monitoring maʼlumotlari topilmadi', ['gnk_id' => $article->gnk_id]);
                                }

                                $article->update([
                                    'program_id' => $monitoringData['data']['result']['data'][0]['project_type_id'],
                                    'sphere_id' => $monitoringData['data']['result']['data'][0]['object_types_id'],
                                    'is_change' => true,
                                ]);
                            }


                            sleep(5);
                        }
                });

        }catch (\Exception $exception){
            Log::info('xatolik: '. $exception->getMessage());
        }
    }
}
