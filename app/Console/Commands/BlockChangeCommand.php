<?php

namespace App\Console\Commands;

use App\Enums\WorkTypeStatusEnum;
use App\Models\Block;
use App\Services\HistoryService;
use App\Services\MonitoringService;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class BlockChangeCommand extends Command
{

    protected $signature = 'app:block-change {id}';

    protected $description = 'Command description';

    private HistoryService $historyService;

    public function __construct(
        protected QuestionService   $questionService,
        protected MonitoringService $monitoringService)
    {
        parent::__construct();
        $this->historyService = new HistoryService('check_list_histories');
    }

    public function handle()
    {

        $block = Block::query()->where('id', $this->argument('id'))->first();

        $workTypes = $this->questionService->getQuestionList($block->id);
        $block = Block::query()->find($block->id);
        $count = 0;
        foreach ($workTypes as $workType) {
            if ($workType['questions'][0]['work_type_status'] == WorkTypeStatusEnum::CONFIRMED) {
                $count += 1;
            }
        }
        if ($count >= count($workTypes)) {
            $block->update([
                'status' => false,
                'is_changed' => true
            ]);
        }

        $block->update([
            'is_changed' => true
        ]);

    }
}
