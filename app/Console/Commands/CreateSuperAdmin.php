<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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

        $user = new User();
        $user->name = 'Super Admin';
        $user->login = 'superadmin';
        $user->password = 'shahzod';
        $user->pinfl = '1234567894551';
        $user->user_status_id = 6;
        $user->save();
        $user->assignRole('admin');
    }
}
