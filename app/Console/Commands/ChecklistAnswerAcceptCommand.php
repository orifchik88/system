<?php

namespace App\Console\Commands;

use App\Enums\CheckListStatusEnum;
use App\Models\CheckListAnswer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChecklistAnswerAcceptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklist:answer-accept';

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

        $this->processChecklistAnswers(
            'inspector_answered_at',
            CheckListStatusEnum::SECOND,
            function ($checklist) {
                $checklist->update([
                    'inspector_answered' => 1,
                    'technic_author_answered_at' => null,
                    'inspector_answered_at' => null,
                ]);
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
                        'technic_author_answered_at' => null,
                    ]);
                }
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
                        'technic_author_answered_at' => null,
                    ]);
                }
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
