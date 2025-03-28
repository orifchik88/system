<?php

namespace App\Console\Commands;

use App\Enums\LogType;
use App\Enums\ObjectStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Helpers\ClaimStatuses;
use App\Models\Article;
use App\Models\ClaimOrganizationReview;
use App\Models\Regulation;
use App\Models\Role;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        $this->testFunction(18128);
        //$this->migrateObjects();
        //$this->migrateRegulations();
    }

    private function migrateRegulations()
    {
        $regulations = Regulation::query()
            ->join('articles', 'articles.id', 'regulations.object_id')
            ->join('regions', 'regions.id', 'articles.region_id')
            ->join('districts', 'districts.id', 'articles.district_id')
            ->whereNotNull('articles.gnk_id')
            ->whereNotNull('regulations.regulation_status_id')
            ->select('regions.soato as region_soato', 'districts.soato as district_soato', 'regulations.regulation_status_id', 'regulations.regulation_number')
            ->get();

        $regulationStatus = [
            RegulationStatusEnum::CONFIRM_REMEDY->value => 'Chora tadbir tasdiqlash',
            RegulationStatusEnum::PROVIDE_REMEDY->value => 'Chora tadbir taqdim qilish',
            RegulationStatusEnum::ATTACH_DEED->value => 'Dalolatnoma biriktirish',
            RegulationStatusEnum::CONFIRM_DEED->value => "Dalolatnoma tasdiqlash",
            RegulationStatusEnum::CONFIRM_DEED_CMR->value => 'Dalolatnoma tasdiqlsh SMR',
            RegulationStatusEnum::ELIMINATED->value => 'Bartaraf etildi',
            RegulationStatusEnum::IN_LAWYER->value => "Ma'muriy adminstratorda",
            RegulationStatusEnum::LATE_EXECUTION->value => "Kechiktirib ijrosi taminlandi",
        ];

        $regulationArr = [];
        foreach ($regulations as $regulation) {
            $regulationArr[$regulation->region_soato][$regulation->district_soato][$regulation->regulation_status_id->value][] = $regulation->regulation_number;
        }

        foreach ($regulationArr as $region => $value) {
            foreach ($value as $district => $item) {
                $request = [
                    'region_soato' => $region,
                    'distict_soato' => $district
                ];
                foreach ($item as $k => $v) {
                    $request['regulations'][] = [
                        'regulation_number' => count($v),
                        'regulation_status' => $regulationStatus[$k]
                    ];
                    list($correlationId, $statusCode) = $this->sendDataToEgov("gasn-regulation-info", $request);
                }
            }
        }

    }

    private function migrateObjects()
    {
        $objects = Article::query()
            ->with(['region', 'district'])
            ->whereNull('egov_id')
            ->limit(20)
            ->get();

        $objectStatus = [
            ObjectStatusEnum::NEW->value => 'Yangi',
            ObjectStatusEnum::PROGRESS->value => 'Jarayonda',
            ObjectStatusEnum::FROZEN->value => 'Muzlatilgan',
            ObjectStatusEnum::SUSPENDED->value => "To'xtatilgan",
            ObjectStatusEnum::SUBMITTED->value => 'Topshirilgan'
        ];

        foreach ($objects as $object) {
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

    private function testFunction($id)
    {
        $reviewObject = ClaimOrganizationReview::with('monitoring')->where('id', $id)->first();

        $apiType = match ($reviewObject->organization_id) {
            15 => 'mchs',
            16 => 'ses',
            17 => 'nogiron',
            18 => 'nogiron2',
            19 => 'kvartira',
            34 => 'ekologiya'
        };

        $apiUrl = "update/id/" . $reviewObject->monitoring->claim->guid . "/action/conclusion-" . $apiType;

        $requestData = [
            $apiType . "_match" => $reviewObject->answerArray[$apiType . "_match"],
            $apiType . "_territory" => $reviewObject->answerArray[$apiType . "_territory"],
            $apiType . "_name" => $reviewObject->answerArray[$apiType . "_name"],
            $apiType . "_conclusion_act" => $reviewObject->answerArray[$apiType . "_conclusion_act"],
            $apiType . "_datetime" => $reviewObject->answerArray[$apiType . "_datetime"]
        ];


        $dataArray['Conclusion' . ucfirst($apiType) . 'V2FormCompletedBuildingsRegistrationCadastral'] = $requestData;
        $response = $this->PostRequest($apiUrl, $dataArray);

        if ($response->status() != 200) {
            echo 'false';
            return false;
        }

        echo 'true';
        return true;
    }

    private
    function PostRequest($url, $data)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->post("https://oldmy.gov.uz/completed-buildings-registration-cadastral-v2/rest-api/" . $url, $data);

        return $response;
    }


}
