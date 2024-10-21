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

class ChecklistAnswerAcceptCommand extends Command
{
//    private HistoryRepositoryInterface $repository;
//
//
//    public function __construct()
//    {
//        parent::__construct();
//        $this->repository = new HistoryRepository('check_list_histories');
//    }
    protected $signature = 'checklist:answer-accept';

    protected $description = 'Command description';

    public function handle()
    {
        CheckListAnswer::where('inspector_answered_at', '<=', Carbon::now())
            ->where('status', CheckListStatusEnum::SECOND)
            ->chunk(50, function ($checklists) {
                foreach ($checklists as $checklist) {
                    $checklist->update([
                        'inspector_answered' => 1,
                        'technic_author_answered_at' => null,
                        'inspector_answered_at' => null,
                    ]);
                }
            });
        CheckListAnswer::query()->where('technic_author_answered_at', '<=', Carbon::now())
            ->where('status', CheckListStatusEnum::FIRST)
            ->whereNull('author_answered')
            ->chunk(50, function ($checklists) {
                foreach ($checklists as $checklist) {
                    if ($checklist->technic_answered)
                    {
                        $checklist->update([
                            'author_answered' => 1,
                        ]);
                    }else{
                        $checklist->update([
                            'author_answered' => 1,
                            'technic_author_answered_at' => null,
                            'inspector_answered_at' => now()->addDays(3)->setTime(23, 59, 59),
                        ]);
                    }

                }
            });

        CheckListAnswer::query()->where('technic_author_answered_at', '<=', Carbon::now())
            ->where('status', CheckListStatusEnum::FIRST)
            ->whereNull('technic_answered')
            ->chunk(50, function ($checklists) {
                foreach ($checklists as $checklist) {
                    if ($checklist->author_answered)
                    {
                        $checklist->update([
                            'author_answered' => 1,
                        ]);
                    }else{
                        $checklist->update([
                            'author_answered' => 1,
                            'technic_author_answered_at' => null,
                            'inspector_answered_at' => now()->addDays(3)->setTime(23, 59, 59),
                        ]);
                    }

                }
            });
    }

//    public function handle()
//    {
//
//        $this->processChecklistAnswers(
//            'inspector_answered_at',
//            CheckListStatusEnum::SECOND,
//            function ($checklist) {
//                $checklist->update([
//                    'inspector_answered' => 1,
//                    'status' => CheckListStatusEnum::CONFIRMED,
//                    'technic_author_answered_at' => null,
//                    'inspector_answered_at' => null,
//                ]);
//
//                $object = Article::query()->find($checklist->object_id);
//                $inspector = $object->users()->where('role_id', UserRoleEnum::INSPECTOR->value)->first();
//
//                $content =  [
//                    'user' => $inspector->id ?? "",
//                    'role' => UserRoleEnum::INSPECTOR->value,
//                    'date' =>  now(),
//                    'status' => $checklist->status->value,
//                    'comment' => 'Automatik ravishda tasdiqlandi',
//                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
//                ];
//
//                $this->saveTable($checklist->id, $content, LogType::TASK_HISTORY);
//            }
//        );
//        $this->processChecklistAnswers(
//            'technic_author_answered_at',
//            CheckListStatusEnum::FIRST,
//            function ($checklist) {
//                if (is_null($checklist->technic_answered)) {
//                    $checklist->update([
//                        'author_answered' => 1,
//                    ]);
//                } else {
//                    $checklist->update([
//                        'author_answered' => 1,
//                        'status' => CheckListStatusEnum::SECOND,
//                        'technic_author_answered_at' => null,
//                    ]);
//                }
//
//                $object = Article::query()->find($checklist->object_id);
//                $author = $object->users()->where('role_id', UserRoleEnum::MUALLIF->value)->first();
//
//                $content =  [
//                    'user' => $author->id ?? "",
//                    'role' => UserRoleEnum::MUALLIF->value,
//                    'date' =>  now(),
//                    'status' => $checklist->status->value,
//                    'comment' => 'Automatik ravishda tasdiqlandi',
//                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
//                ];
//                $this->saveTable($checklist->id, $content, LogType::TASK_HISTORY);
//
//            },
//            ['author_answered' => null]
//        );
//
//        $this->processChecklistAnswers(
//            'technic_author_answered_at',
//            CheckListStatusEnum::FIRST,
//            function ($checklist) {
//                if (is_null($checklist->author_answered)) {
//                    $checklist->update([
//                        'author_answered' => 1,
//                    ]);
//                } else {
//                    $checklist->update([
//                        'author_answered' => 1,
//                        'status' => CheckListStatusEnum::SECOND,
//                        'technic_author_answered_at' => null,
//                    ]);
//                }
//
//                $object = Article::query()->find($checklist->object_id);
//                $technic = $object->users()->where('role_id', UserRoleEnum::TEXNIK->value)->first();
//
//                $content =  [
//                    'user' => $technic->id ?? "",
//                    'role' => UserRoleEnum::TEXNIK->value,
//                    'date' =>  now(),
//                    'status' => $checklist->status->value,
//                    'comment' => 'Automatik ravishda tasdiqlandi',
//                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
//                ];
//                $this->saveTable($checklist->id, $content, LogType::TASK_HISTORY);
//            },
//            ['technic_answered' => null]
//        );
//
//        echo "bajarildi";
//    }

//    private function processChecklistAnswers($dateField, $status, $callback, $additionalConditions = [])
//    {
//        CheckListAnswer::query()
//            ->where($dateField, '<=', Carbon::now())
//            ->where('status', $status)
//            ->where($additionalConditions)
//            ->chunk(50, function ($checklists) use ($callback) {
//                foreach ($checklists as $checklist) {
//                    $callback($checklist);
//                }
//            });
//    }

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
