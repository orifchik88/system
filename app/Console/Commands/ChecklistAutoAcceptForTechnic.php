<?php

namespace App\Console\Commands;

use App\Enums\CheckListStatusEnum;
use App\Enums\LogType;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\CheckListAnswer;
use App\Repositories\HistoryRepository;
use App\Repositories\Interfaces\HistoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChecklistAutoAcceptForTechnic extends Command
{
    private HistoryRepositoryInterface $repository;


    public function __construct()
    {
        parent::__construct();
        $this->repository = new HistoryRepository('check_list_histories');
    }
    protected $signature = 'checklist:answer-accept-technic';

    protected $description = 'Command description';

    public function handle()
    {

        CheckListAnswer::query()->where('technic_author_answered_at', '<=', Carbon::now())
            ->where('status', CheckListStatusEnum::FIRST)
            ->whereNull('technic_answered')
            ->whereNot('work_type_id', 14)
            ->chunk(50, function ($checklists) {
                foreach ($checklists as $checklist) {
                    if ($checklist->author_answered)
                    {
                        $checklist->update([
                            'status' => CheckListStatusEnum::SECOND,
                            'technic_answered' => 1,
                            'technic_author_answered_at' => null,
                            'inspector_answered_at' => now()->addDays(5)->setTime(23, 59, 59),
                        ]);

                    }else{
                        $checklist->update([
                            'technic_answered' => 1,
                        ]);
                    }

                    $this->saveHistory(UserRoleEnum::TEXNIK->value, $checklist);

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
            'comment' => 'Automatik ravishda tasdiqlandi',
            'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
        ];

        $this->saveTable($checklist->id, $content, LogType::TASK_HISTORY);
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
