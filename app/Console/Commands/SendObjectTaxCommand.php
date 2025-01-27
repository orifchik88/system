<?php

namespace App\Console\Commands;

use App\Enums\ConstructionWork;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendObjectTaxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-object-tax-command';

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
        Article::query()
            ->whereNull('old_id')
            ->where('object_status_id', ObjectStatusEnum::PROGRESS)
            ->chunk(50, function ($articles){
                foreach ($articles as $article) {
                    $this->sendTax($article);
                }
            });
    }

    private function sendTax($object)
    {
        try{
            $authUsername = config('app.mygov.login');
            $authPassword = config('app.mygov.password');
            $customer = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
            $builder = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

            $data = [
                'object_id' => $object->task_id,
                'cadastral_number' => $object->cadastral_number,
                'construction_type_name' => $object->construction_works,
                'construction_type_id' => ConstructionWork::fromString($object->construction_works)->value,
                'created_at' => $object->created_at,
                'customer_name' => $customer ? $customer->organization_name : '',
                'district_soato' => $object->district->soato ?? '',
                'general_contractor' => $builder ? $builder->organization_name : '',
                'not_completed_construction' => '',
                'object_name' => $object->name,
                'open_date' => $object->deadline,
                'pinfl_customer' => $customer->name ? $customer->pinfl : '',
                'pinfl_general_contractor' => $builder->name ? $builder->pinfl : '',
                'price_construction_installation' => $object->construction_cost,
                'region_soato' => $object->region->soato,
                'send_date' => Carbon::now(),
                'send_id' => $object->id,
                'tin_customer' => $customer->name ? '' : $customer->pinfl,
                'tin_general_contractor' => $builder->name ? '' : $builder->pinfl,
            ];

            Http::withBasicAuth($authUsername, $authPassword)->post('https://api.shaffofqurilish.uz/api/v1/constructionSave', $data);
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
        }
    }
}
