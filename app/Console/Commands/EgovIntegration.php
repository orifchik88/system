<?php

namespace App\Console\Commands;

use App\Enums\ObjectStatusEnum;
use App\Models\Article;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class EgovIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:egov-integration';

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
        $objects = Article::query()
            ->with(['region', 'district'])
            ->whereNull('egov_id')
            ->limit(20)
            ->get();

        foreach ($objects as $object) {
            $objectStatus = [
                ObjectStatusEnum::NEW->value => 'Yangi',
                ObjectStatusEnum::PROGRESS->value => 'Jarayonda',
                ObjectStatusEnum::FROZEN->value => 'Muzlatilgan',
                ObjectStatusEnum::SUSPENDED->value => "To'xtatilgan",
                ObjectStatusEnum::SUBMITTED->value => 'Topshirilgan'
            ];

            $data = [
                'object_name' => $object->name,
                'region_soato' => $object->region->soato,
                'district_soato' => $object->district->soato,
                'object_address' => $object->location_building,
                'lat' => $object->lat,
                'long' => $object->long,
                'object_status' => $objectStatus[$object->object_status_id->value],
                'internal_controller_stir' => ($object->users()->where('role_id', 10)->first() != null) ? $object->users()->where('role_id', 10)->first()->identification_number : '-'
            ];

            list($correlationId, $statusCode) = $this->sendDataToEgov("gasn-object-info", $data);

            if ($statusCode == 202 || $statusCode == 200 || $statusCode == 201)
                $object->update(['egov_id' => $correlationId]);
        }

    }

    private function sendDataToEgov($requestType, $data)
    {
        $correlationId = $this->gen_uuid();
        $requestData = [
            "Request" => $requestType,
            "correlationId" => $correlationId,
            "destinationSubscribers" => ["bi_egov_uz"],
            "data" => $data
        ];

        $client = new Client();
        $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');
        $url = "https://api.shaffofqurilish.uz/api/v1/request/egov-push-object";

        $resClient = $client->post($url,
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                ],
                'json' => $requestData
            ]
        );

        return [$correlationId, $resClient->getStatusCode()];
    }

    private function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
