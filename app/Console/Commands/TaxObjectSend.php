<?php

namespace App\Console\Commands;

use App\Enums\ConstructionWork;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Services\ArticleService;
use App\Services\MyGovService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaxObjectSend extends Command
{

    protected $signature = 'tax:send';

    public function __construct(protected  MyGovService $service)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $authUsername = config('app.passport.login');
            $authPassword = config('app.passport.password');

            Article::query()
                ->whereBetween('created_at', ['2025-01-01 00:00:00', '2025-02-28 23:59:59'])
                ->whereNull('send_tax')
                ->each(function ($article) use ($authUsername, $authPassword) {
                    $data = $this->service->getObjectTax($article->id);

                    Http::withBasicAuth($authUsername, $authPassword)
                        ->post('https://api.shaffofqurilish.uz/api/v1/constructionSave', $data);

                    $article->update(['send_tax' => true]);
                });

        } catch (\Exception $exception) {
            echo $exception->getMessage();
            Log::error('Tax Error: ' . $exception->getMessage());
        }
    }

}
