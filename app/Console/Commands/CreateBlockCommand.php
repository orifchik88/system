<?php

namespace App\Console\Commands;

use App\Enums\BlockModeEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\WorkTypeStatusEnum;
use App\Models\Article;
use App\Models\Block;
use App\Services\HistoryService;
use App\Services\MonitoringService;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class CreateBlockCommand extends Command
{
    private HistoryService $historyService;

    public function __construct(
        protected QuestionService   $questionService,
        protected MonitoringService $monitoringService)
    {
        parent::__construct();
        $this->historyService = new HistoryService('check_list_histories');
    }
    protected $signature = 'app:create-block-command';


    protected $description = 'Command description';


    public function handle()
    {

        $blocks = Block::whereIn('id', function ($query) {
            $query->select('block_id')
                ->from('check_list_answers');
        })
            ->where('status', true)
            ->where('selected_work_type', false)
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
                            'selected_work_type' => true,
                            'is_changed' => true
                        ]);
                    }
                }
            });


//        Article::query()
//            ->whereDoesntHave('blocks')
////            ->where('region_id', 1)
//            ->whereIn('object_status_id', [
//                ObjectStatusEnum::SUSPENDED,
//                ObjectStatusEnum::FROZEN,
//                ObjectStatusEnum::PROGRESS
//            ])
//            ->chunk(100, function ($objects) {
//                foreach ($objects as $object) {
//                    Block::create([
//                        'name' => 'A',
//                        'block_mode_id' => $object->object_type_id == 1
//                            ? BlockModeEnum::TARMOQ
//                            : BlockModeEnum::BINO,
//                        'article_id' => $object->id,
//                        'status' => true,
//                        'accepted' => false,
//                        'selected_work_type' => false
//                    ]);
//                }
//            });
    }

}
