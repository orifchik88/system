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

class ChecklistAnswerAcceptCommand extends Command
{
    private HistoryRepositoryInterface $repository;


    public function __construct()
    {
        parent::__construct();
        $this->repository = new HistoryRepository('check_list_answer');
    }
    protected $signature = 'checklist:answer-accept';

    protected $description = 'Command description';

    public function handle()
    {

        $this->processChecklistAnswers(
            'inspector_answered_at',
            CheckListStatusEnum::SECOND,
            function ($checklist) {
                $checklist->update([
                    'inspector_answered' => 1,
                    'status' => CheckListStatusEnum::CONFIRMED,
                    'technic_author_answered_at' => null,
                    'inspector_answered_at' => null,
                ]);

                $object = Article::query()->find($checklist->object_id);
                $inspector = $object->users()->where('role_id', UserRoleEnum::INSPECTOR->value)->first();

                $content =  [
                    'user' => $inspector->id ?? "",
                    'role' => UserRoleEnum::INSPECTOR->value,
                    'date' =>  now(),
                    'status' => $checklist->status->value,
                    'comment' => 'Automatik ravishda tasdiqlandi',
                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
                ];
                return $this->repository->createHistory(guId: $checklist->id, content: $content, type: LogType::TASK_HISTORY);

            }
        );
        $this->processChecklistAnswers(
            'technic_author_answered_at',
            CheckListStatusEnum::FIRST,
            function ($checklist) {
                if (is_null($checklist->technic_answered)) {
                    $checklist->update([
                        'author_answered' => 1,
                    ]);
                } else {
                    $checklist->update([
                        'author_answered' => 1,
                        'status' => CheckListStatusEnum::SECOND,
                        'technic_author_answered_at' => null,
                    ]);
                }

                $object = Article::query()->find($checklist->object_id);
                $author = $object->users()->where('role_id', UserRoleEnum::MUALLIF->value)->first();

                $content =  [
                    'user' => $author->id ?? "",
                    'role' => UserRoleEnum::MUALLIF->value,
                    'date' =>  now(),
                    'status' => $checklist->status->value,
                    'comment' => 'Automatik ravishda tasdiqlandi',
                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
                ];

                return $this->repository->createHistory(guId: $checklist->id, content: $content, type: LogType::TASK_HISTORY);
            },
            ['author_answered' => null]
        );

        $this->processChecklistAnswers(
            'technic_author_answered_at',
            CheckListStatusEnum::FIRST,
            function ($checklist) {
                if (is_null($checklist->author_answered)) {
                    $checklist->update([
                        'author_answered' => 1,
                    ]);
                } else {
                    $checklist->update([
                        'author_answered' => 1,
                        'status' => CheckListStatusEnum::SECOND,
                        'technic_author_answered_at' => null,
                    ]);
                }

                $object = Article::query()->find($checklist->object_id);
                $technic = $object->users()->where('role_id', UserRoleEnum::TEXNIK->value)->first();

                $content =  [
                    'user' => $technic->id ?? "",
                    'role' => UserRoleEnum::TEXNIK->value,
                    'date' =>  now(),
                    'status' => $checklist->status->value,
                    'comment' => 'Automatik ravishda tasdiqlandi',
                    'additionalInfo' => ['user_answered' => 1, 'answered' => 'auto']
                ];

                return $this->repository->createHistory(guId: $checklist->id, content: $content, type: LogType::TASK_HISTORY);
            },
            ['technic_answered' => null]
        );

        echo "bajarildi";
    }

    private function processChecklistAnswers($dateField, $status, $callback, $additionalConditions = [])
    {
        CheckListAnswer::query()
            ->where($dateField, '<=', Carbon::now())
            ->where('status', $status)
            ->where($additionalConditions)
            ->chunk(50, function ($checklists) use ($callback) {
                foreach ($checklists as $checklist) {
                    $callback($checklist);
                }
            });
    }
}
