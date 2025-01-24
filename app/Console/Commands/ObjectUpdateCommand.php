<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $articles = Article::query()
                ->whereRaw('gnk_id REGEXP "^[0-9]+$"')
                ->whereBetween('gnk_id', [200000, 300000])
                ->where('created_at', '>=', '2024-01-01 00:00:00')
                ->whereNotNull('old_id')
                ->where('is_changed', false)
                ->chunk(10, function ($articles) {
                    DB::transaction(function () use ($articles) {
                        foreach ($articles as $article) {
                            $tenderData = getData(config('app.gasn.tender'), $article->reestr_number);
                            if (!$tenderData || !isset($tenderData['data']['result']['data'])) {
                                Log::warning('Tender maʼlumotlari topilmadi', ['reestr_number' => $article->reestr_number]);
                                continue;
                            }

                            $article->update([
                                'gnk_id' => $tenderData['data']['result']['data']['gnk_id'],
                                'funding_source_id' => $tenderData['data']['result']['data']['finance_source'],
                            ]);

                            $monitoringData = getData(config('app.gasn.get_monitoring'), $article->gnk_id);
                            if (!$monitoringData || !isset($monitoringData['data']['result']['data'][0])) {
                                Log::warning('Monitoring maʼlumotlari topilmadi', ['gnk_id' => $article->gnk_id]);
                                continue;
                            }

                            $article->update([
                                'program_id' => $monitoringData['data']['result']['data'][0]['project_type_id'],
                                'sphere_id' => $monitoringData['data']['result']['data'][0]['object_types_id'],
                            ]);
                        }
                    });
                });

        }catch (\Exception $exception){
            Log::info('xatolik: '. $exception->getMessage());
        }
    }
}
