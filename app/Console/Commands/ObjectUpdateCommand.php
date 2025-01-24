<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
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
                ->where('created_at', '>=', '2024-01-01 00:00:00')
                ->whereNotNull('old_id')
                ->where('is_changed', false)
                ->chunk(10, function ($articles) {
                    foreach ($articles as $article) {
                        $data = getData(config('app.gasn.tender'), $article->reestr_number);
                        $article->update([
                            'gnk_id' => $data['data']['result']['data']['gnk_id'],
                            'funding_source_id' => $data['data']['result']['data']['finance_source']
                        ]);

                        $data = getData(config('app.gasn.get_monitoring'), $article->gnk_id);
                        $article->update([
                            'program_id' => $data['data']['result']['data'][0]['project_type_id'],
                            'sphere_id' => $data['data']['result']['data'][0]['object_types_id']
                        ]);

                    }
                });
        }catch (\Exception $exception){
            Log::info('xatolik: '. $exception->getMessage());
        }
    }
}
