<?php

namespace App\Console\Commands;

use App\Enums\LogType;
use App\Helpers\ClaimStatuses;
use App\Models\Claim;
use App\Services\ArticleService;
use App\Services\ClaimService;
use App\Services\HistoryService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class SendToMinstroyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-to-minstroy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(
        ClaimService   $claimService,
        ArticleService $articleService
    )
    {
        parent::__construct();
        $this->claimService = $claimService;
        $this->articleService = $articleService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $historyService = new HistoryService('claim_histories');
        $claims = Claim::query()
            ->where(
                [
                    'is_minstroy' => true,
                    'status' => ClaimStatuses::TASK_STATUS_ACCEPTANCE
                ]
            )
            ->get();

        foreach ($claims as $claim) {
            $expiryDate = $this->claimService->getExpirationDate(startDate: $claim->created_at, duration: 5);

            if (Carbon::now() > $expiryDate) {
                try {
                    $client = new Client();
                    $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');
                    $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/ccnis-dxa-watcher-type?conclusion=' . $claim->number_conclusion_project,
                        [
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                            ]
                        ]);

                    $response = json_decode($resClient->getBody(), true);
                    $conclusions = $response['result']['data']['conclusions'];

                    if ($response['result']['data']['success'] && count($conclusions) == 1) {
                        if ($conclusions[0]['watcher_type'] == 1) {
                            $dataArray['SendObjectToMinstroyV2FormCompletedBuildingsRegistrationCadastral'] = [
                                'comment_to_send_minstroy' => 'Tuman (shahar) qurilish va uy-joy kommunal xo`jaligi bo‘limlari qurilish-montaj ishlari tugallangan ikki qavatdan yuqori bo‘lmagan (sokolni hisobga olmagan holda), balandligi yer
                                yuzasidan 12 metrdan va (yoki) umumiy maydoni 500 kvadrat metrdan ortiq bo‘lmagan yakka tartibdagi uy-joylar va 300 metr kubdan ortiq bo‘lmagan noturar bino va inshootlardan (keyingi o‘rinlarda — I toifa obyektlar)
                                foydalanish uchun ruxsatnoma beradilar',
                            ];

                            $response = $this->PostRequest("update/id/" . $claim->guid . "/action/send-object-to-minstroy", $dataArray);
                            if ($response->status() != 200) {
                                return false;
                            }

                            $claim->update(
                                [
                                    'status' => ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG,
                                    'end_date' => Carbon::now()->format('Y-m-d H:i:s')
                                ]
                            );

                            $historyService->createHistory(
                                guId: $claim->guid,
                                status: ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG,
                                type: LogType::TASK_HISTORY,
                                date: null,
                                comment: $dataArray['SendObjectToMinstroyV2FormCompletedBuildingsRegistrationCadastral']['comment_to_send_minstroy']
                            );
                        }
                    }
                } catch (\Exception $exception) {

                }
            }
        }
    }

    private
    function PostRequest($url, $data)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->post($this->url . $url, $data);

        return $response;
    }
}
