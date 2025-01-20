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

    protected $signature = 'app:block-change';

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

        $blocks = Block::whereIn('id', function ($query) {
            $query->select('block_id')
                ->from('check_list_answers');
        })
            ->where('status', true)
            ->where('selected_work_type', true)
            ->chunk(10, function ($blocks) {
                foreach ($blocks as $block) {
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
                }
            });

    }
}
