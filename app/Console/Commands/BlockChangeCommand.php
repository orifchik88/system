<?php

namespace App\Console\Commands;

use App\Enums\WorkTypeStatusEnum;
use App\Models\ActViolation;
use App\Models\Block;
use App\Models\Monitoring;
use App\Services\HistoryService;
use App\Services\MonitoringService;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class BlockChangeCommand extends Command
{

    protected $signature = 'app:monitoring-update';

    protected $description = 'Command description';


    public function __construct(
        protected QuestionService   $questionService,
        protected MonitoringService $monitoringService)
    {
        parent::__construct();
    }

    public function handle()
    {

        Monitoring::query()
            ->whereNotNull('question_64')
            ->orWhereNotNull('question_65')
            ->chunk(500, function ($monitorings) {
                foreach ($monitorings as $monitoring) {
                    $meta = [];
                    if (!is_null($monitoring->question_64)) {
                        $meta['64'] = '5';
                    }
                    if (!is_null($monitoring->question_65)) {
                        $meta['65'] = '5';
                    }
                    $monitoring->update([
                        'constant_checklist' => json_encode($meta),
                    ]);
                }
            });


    }
}
