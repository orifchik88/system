<?php

namespace App\Console\Commands;

use App\Models\DxaResponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ResponseCreated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:response {task_id}';

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

        $url = "https://my.gov.uz/notice-beginning-construction-works-v4/rest-api";

        $response = Http::withBasicAuth('qurilish.sohasida.nazorat.inspeksiya.201122919', 'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_')
        ->get('https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/get-task?id='.$this->argument('task_id'));

        $data = $response->body();
        $res = new DxaResponse();
        $res->task = $data;
        $res->save();

    }
}
