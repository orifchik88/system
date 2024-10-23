<?php

namespace App\Console\Commands;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin';

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
        User::query()->create([
            'login' => 'shaffof',
            'password' => '$h@ffof2024',
            'user_status_id' => UserStatusEnum::ACTIVE,
        ]);
    }
}
