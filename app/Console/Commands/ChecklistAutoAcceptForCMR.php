<?php

namespace App\Console\Commands;

use App\Enums\CheckListStatusEnum;
use App\Enums\LogType;
use App\Enums\UserRoleEnum;
use App\Enums\WorkTypeStatusEnum;
use App\Models\Article;
use App\Models\Block;
use App\Models\CheckListAnswer;
use App\Repositories\HistoryRepository;
use App\Repositories\Interfaces\HistoryRepositoryInterface;
use App\Services\QuestionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChecklistAutoAcceptForCMR extends Command
{
    private HistoryRepositoryInterface $repository;


    public function __construct(protected QuestionService $questionService)
    {
        parent::__construct();
        $this->repository = new HistoryRepository('check_list_histories');
    }

    protected $signature = 'checklist:answer-accept-crm';

    protected $description = 'Command description';

    public function handle()
    {
        CheckListAnswer::where('inspector_answered_at', '<=', Carbon::now())
            ->where('status', CheckListStatusEnum::AUTO_CONFIRMED)
            ->where('work_type_id', '!=', 14)
            ->where('auto_confirmed', true)
            ->chunk(50, function ($checklists) {
                foreach ($checklists as $checklist) {
                        $checklist->update([
                            'status' => CheckListStatusEnum::CONFIRMED,
                            'inspector_answered' => 1,
                            'technic_author_answered_at' => null,
                            'inspector_answered_at' => null,
                        ]);
                    $this->saveHistory(UserRoleEnum::INSPECTOR->value, $checklist);
                }
            });

    }

    private function saveHistory($roleId, $checklist)
    {
        $object = Article::query()->find($checklist->object_id);
        $user = $object->users()->where('role_id', $roleId)->first();

        $content =  [
            'user' => $user->id ?? "",
            'role' => $roleId,
            'date' =>  now(),
            'status' => $checklist->status->value,
            'comment' => 'SMR tomonidan automatik ravishda tasdiqlandi',
            'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
        ];

        $this->updateBlock($checklist);

        $this->saveTable($checklist->id, $content, LogType::TASK_HISTORY);
    }

    private function updateBlock($checklist)
    {
        $workTypes = $this->questionService->getQuestionList($checklist->block_id);
        $block = Block::query()->find($checklist->block_id);
        $count = 0;
        foreach ($workTypes as $workType) {
            if ($workType['questions'][0]['work_type_status'] == WorkTypeStatusEnum::CONFIRMED) {
                $count += 1;
            }
        }
        if ($count == count($workTypes)) {
            $block->update([
                'status' => false
            ]);
        }
    }

    private function saveTable($guId, $content, $type)
    {
        DB::table('check_list_histories')->insertGetId([
            'gu_id' => $guId,
            'content' => json_encode($content),
            'type' => $type,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
