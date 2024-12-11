<?php

namespace App\Console\Commands;

use App\Enums\BlockModeEnum;
use App\Enums\ObjectStatusEnum;
use App\Models\Article;
use App\Models\Block;
use Illuminate\Console\Command;

class CreateBlockCommand extends Command
{
    protected $signature = 'app:create-block-command';


    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Article::query()
            ->whereDoesntHave('blocks')
            ->where('region_id', 1)
            ->whereIn('object_status_id', [
                ObjectStatusEnum::SUSPENDED,
                ObjectStatusEnum::FROZEN,
                ObjectStatusEnum::PROGRESS
            ])
            ->chunk(100, function ($objects) {
                foreach ($objects as $object) {
                    Block::create([
                        'name' => 'A',
                        'block_mode_id' => $object->object_type_id == 1
                            ? BlockModeEnum::TARMOQ
                            : BlockModeEnum::BINO,
                        'article_id' => $object->id,
                        'status' => true,
                        'accepted' => false,
                        'selected_work_type' => false
                    ]);
                }
            });
    }

}
