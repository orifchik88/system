<?php

namespace App\Console\Commands;

use App\Enums\ConstructionWork;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TaxObjectSend extends Command
{

    protected $signature = 'tax:send';


    protected $description = 'Command description';


    public function handle()
    {
        $object = Article::query()->find(1);
        $authUsername = config('app.mygov.login');
        $authPassword = config('app.mygov.password');
        $customer = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $builder = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        dd($object->blocks);

        $data = [
            'send_id' => $object->id,
            'send_date' => Carbon::now(),
            'cadastral_number' => $object->cadastral_number,
            'construction_type_name' => $object->construction_works,
            'construction_type_id' => ConstructionWork::fromString($object->construction_works)->value,
            'created_at' => $object->created_at,
            'customer_name' => $customer ? $customer->organization_name : '',
            'district_soato' => $object->district->soato ?? '',
            'general_contractor' => $builder ? $builder->organization_name : '',
            'not_completed_construction' => '',
            'object_name' => $object->name,
            'pinfl_customer' => $customer->name ? $customer->pinfl : '',
            'pinfl_general_contractor' => $builder->name ? $builder->pinfl : '',
            'price_construction_installation' => $object->construction_cost,
            'region_soato' => $object->region ? $object->region->soato : null,
            'tin_customer' => $customer->name ? '' : $customer->pinfl,
            'tin_general_contractor' => $builder->name ? '' : $builder->pinfl,
        ];

        Http::withBasicAuth($authUsername, $authPassword)->post('https://api.shaffofqurilish.uz/api/v1/constructionSave', $data);

    }
}
