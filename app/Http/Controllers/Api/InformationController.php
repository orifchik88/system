<?php

namespace App\Http\Controllers\Api;

use App\Exports\DxaResponseExport;
use App\Http\Resources\BasisResource;
use App\Http\Resources\NormativeDocumentResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\SphereResource;
use App\Http\Resources\TopicResource;
use App\Models\Article;
use App\Models\Basis;
use App\Models\NormativeDocument;
use App\Models\NotificationLog;
use App\Models\Program;
use App\Models\Sphere;
use App\Models\Topic;
use App\Models\User;
use App\Services\InformationService;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InformationController extends BaseController
{
//    public function __construct(protected InformationService $informationService)
//    {
//        $this->middleware('auth');
//        parent::__construct();
//    }

    public function monitoringObjects(): JsonResponse
    {
        $customerInn = request('customer_inn');
        $pudratInn = request('pudrat_inn');

        $informationService = new InformationService();
        try {
            $data = getData(config('app.gasn.monitoring'), request('expertise_number'))['data']['result']['data'];

            if (!empty($data))
            {
                if ($data[0]['end_term_work_days']){
                    $meta[] = $informationService->customer($customerInn, $pudratInn);
                }
                $sphere = Sphere::query()->find($data[0]['object_types_id']);
                $program = Program::query()->find($data[0]['project_type_id']);
                $meta[] = [
                    'id' => $data[0]['id'],
                    'gnk_id' => $data[0]['gnk_id'],
                    'project_type' => ProgramResource::make($program),
                    'name' => $data[0]['name'],
                    'end_term_work_days' => $data[0]['end_term_work_days'],
                    'sphere' => SphereResource::make($sphere),
                ];
            }else{
                $meta[] = $informationService->customer($customerInn, $pudratInn);
            }


            return $this->sendSuccess($meta, 'Monitoring objects successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function statement(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.bayonnoma'), \request('conc'));
            return $this->sendSuccess($data['data']['conclusions'], 'Tender');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function tender(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.tender'), \request('conc'));
            return $this->sendSuccess($data['data']['result']['data'], 'Tender');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function monitoringCustomer(): JsonResponse
    {
        try {
            $customerInn = request('customer_inn');
            $pudratInn = request('pudrat_inn');
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $url = 'https://api.shaffofqurilish.uz/api/v1/request/monitoring-objects?customer_inn='.$customerInn.'&pudrat_inn='.$pudratInn;


            $resClient = $client->post($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);
            $response = json_decode($resClient->getBody(), true);

            if (isset($response['result']['data']['result']['data'])) {
                foreach ($response['result']['data']['result']['data'] as &$item) {
                    if (isset($item['pudrat_tender']) && is_array($item['pudrat_tender'])) {
                        $item['pudrat_tender'] = array_values($item['pudrat_tender']);

                        $item['pudrat_tender'] = array_filter($item['pudrat_tender'], function ($tender) use ($pudratInn) {
                            return $tender['winner_inn'] == $pudratInn;
                        });

                        if (!empty($item['pudrat_tender'])) {
                            usort($item['pudrat_tender'], function ($a, $b) {
                                return strtotime($b['confirmed_date']) - strtotime($a['confirmed_date']);
                            });

                            $item['pudrat_tender'] = [reset($item['pudrat_tender'])];
                        } else {
                            $item['pudrat_tender'] = [];
                        }

                        if (empty($item['pudrat_tender'])) {
                            unset($item);
                        }
                    }
                }

                $response['result']['data']['result']['data'] = array_values($response['result']['data']['result']['data']);
            }

            $data = $response['result']['data']['result']['data'][0];

            $meta = [
                'id' => $data['id'],
                'gnk_id' => $data['gnk_id'],
                'project_type_id' => $data['project_type_id'],
                'name' => $data['name'],
                'end_term_work_days' => $data['end_term_work_days']  ?? $data['pudrat_tender'][0]['end_term_work_days'],
            ];


            return $this->sendSuccess($meta, 'Monitoring customer information successfully.');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reestr(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.reestr'), \request('reestr_number'));
            return $this->sendSuccess($data['data'], 'Reestr');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
    public function monitoringGNK(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.get_monitoring'), request('gnk_id'));
            return $this->sendSuccess($data['data']['result']['data'], 'Object');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function rating(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.rating'), \request('inn'));
            return $this->sendSuccess($data['data']['data'], 'Reyting');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function conference(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.conference'), \request('conc'));
            return $this->sendSuccess($data['data'], 'Kengash');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function expertiseFiles(): JsonResponse
    {
        try {
            $data = getData(config('app.gasn.expertise'), \request('reestr_number'));
            return $this->sendSuccess($data['data']['data'], 'Expertise Files');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function checkUser(): JsonResponse
    {
        try {
            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_authorization_code
            &client_id='.config('services.oneId.id').
            '&client_secret='.config('services.oneId.secret').
            '&code='.request('code').
            '&redirect_url='.config('services.oneId.redirect');
            $resClient = Http::post($url);
            $response = json_decode($resClient->getBody(), true);


            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_access_token_identify
            &client_id='.config('services.oneId.id').
            '&client_secret='.config('services.oneId.secret').
            '&access_token='.$response['access_token'].
            '&scope='.$response['scope'];
            $resClient = Http::post($url);
            $data = json_decode($resClient->getBody(), true);


            $user = User::query()->where('pinfl', $data['pin'])->first();

            if (!$user) throw new ModelNotFoundException('Foydalanuvchi topilmadi');
            if ($user->active == 0) throw new ModelNotFoundException('Foydalanuvchi faol emas');

            $user->update([
                'notification_app_id' => request('app_id') ?? null,
            ]);

            $combinedData = $data['pin'] . ':' . $response['access_token'];

            $encodedData = base64_encode($combinedData);

            $meta = [
                'roles'=>RoleResource::collection($user->roles),
                'access_token' => $encodedData,
                'full_name' => $user->full_name
            ];

            return $this->sendSuccess($meta, 'Success');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function time(): JsonResponse
    {
        return $this->sendSuccess(now(), 'Now');
    }

    public function getRepoPDF()
    {
        try {
            $informationService = new InformationService();
            $data = $informationService->getConclusionPDF(request('task_id'));
            return $this->sendSuccess($data, 'Conclusion PDF');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function normativeDocs(): JsonResponse
    {
        try {
            $docs = NormativeDocument::query()->paginate(request('per_page', 10));

            return $this->sendSuccess(NormativeDocumentResource::collection($docs), 'Normative', pagination($docs));

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function topics(): JsonResponse
    {
        try {
            $topics = Topic::query()
                ->where('parent_id', request('doc_id'))
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(TopicResource::collection($topics), 'Topics', pagination($topics));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function basis(): JsonResponse
    {
        try {
            $topics = Basis::query()
                ->where('parent_id', request('topic_id'))
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(BasisResource::collection($topics), 'Basis', pagination($topics));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function notifications()
    {
        try {
            $user = Auth::user();
            $query = $user->notifications();
            if (!is_null(request('read'))) {
                $query->where('read', request('read'));
            }
            $notifications = $query->orderBy('id', 'DESC')->paginate(request('per_page', 10));
            return $this->sendSuccess(NotificationResource::collection($notifications), 'Notifications', pagination($notifications));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function notificationCount()
    {
        try {
            $user = Auth::user();
            $data =  [
                'all' => $user->notifications()->count(),
                'notRead' => $user->notifications()->where('read', false)->count(),
            ];
            return $this->sendSuccess($data, 'Notifications');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function notificationRead(): JsonResponse
    {
        try {
             NotificationLog::query()->find(request('id'))->update(['read' => true]);
             return $this->sendSuccess([],'Notification Read');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function qrImage($id): JsonResponse
    {
        try {
            $article = Article::query()->find($id);

            $url = URL::to('/object-info').'/'.$article->task_id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($url));

            return $this->sendSuccess($qrImage, 'Qr Image');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getRegisterExcel($type): JsonResponse
    {
        try {
            $excelContent = Excel::raw(new DxaResponseExport($type), \Maatwebsite\Excel\Excel::XLSX);

            $base64File = base64_encode($excelContent);

            return $this->sendSuccess($base64File, 'Excel');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

}
