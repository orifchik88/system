<?php

namespace App\Services;

use App\Enums\LogType;
use App\Helpers\ClaimStatuses;
use App\Http\Requests\ClaimRequests\AcceptTask;
use App\Http\Requests\ClaimRequests\AttachObject;
use App\Http\Requests\ClaimRequests\ClaimSendToMinstroy;
use App\Models\Response;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class ClaimService
{
    private ClaimRepositoryInterface $claimRepository;
    private HistoryService $historyService;
    private string $url = 'https://my.gov.uz/completed-buildings-registration-cadastral-v2/rest-api/';

    public function __construct(
        ClaimRepositoryInterface $claimRepository,
    )
    {
        $this->claimRepository = $claimRepository;
        $this->historyService = new HistoryService('claim_histories');
    }

    public function getActiveResponses()
    {
        return $this->claimRepository->getActiveResponses();
    }

    public function getExpiredTaskList()
    {
        return $this->claimRepository->getExpiredTaskList();
    }


    public function getExpirationDate(string $startDate, int $duration): null|string
    {
        for ($day = 1; $day <= $duration; $day++) {
            $current = Carbon::parse($startDate)->addDays($day)->format('d-m-Y');

            if (Carbon::parse($startDate)->addDays($day)->isWeekend() || in_array($current, config('app.holidays'))) {
                $duration++;
            }
        }

        return Carbon::parse($startDate)->addDays($duration)->toDateString();
    }

    public function taskIsExpired(string $expirationDate): bool
    {
        $today = Carbon::today()->timestamp;
        $expirationDate = Carbon::parse($expirationDate)->timestamp;

        return $expirationDate <= $today;
    }

    public function updateTaskExpiration(int $guId, bool $isExpired, string $expirationDate): bool
    {
        return $this->claimRepository->updateClaim(
            guId: $guId,
            data: [
                'expired' => ($isExpired) ? 1 : 0,
                'expiry_date' => $expirationDate
            ]
        );
    }

    public function getClaimById(int $id)
    {
        return $this->claimRepository->getClaimById(id: $id);
    }

    public function getObjects(int $id)
    {
        return $this->claimRepository->getObjects(id: $id);
    }

    public function getClaimByGUID(int $guid)
    {
        return $this->claimRepository->getClaimByGUID(guid: $guid);
    }


    public function getStatistics(?int $regionId, ?int $districtId)
    {
        return $this->claimRepository->getStatistics(regionId: $regionId, districtId:$districtId);
    }

    public function getTaskStatisticsRepeated(int $region = null): array
    {
        return $this->claimRepository->getStatisticsRepeated($region);
    }

    public function getStatisticsCount(
        ?int $regionId,
        ?int $expired,
        ?string $dateFrom,
        ?string $dateTo,
    ) {
        return $this->claimRepository->getStatisticsCount(
            regionId: $regionId,
            expired: $expired,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
    }

    public function getTaskList(
        ?int    $regionId,
        ?int    $task_id,
        ?string $name,
        ?string $customer,
        ?string $sender,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $expired,
    ){
        return $this->claimRepository->getList(
            regionId: $regionId,
            task_id: $task_id,
            name: $name,
            customer: $customer,
            sender: $sender,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            expired: $expired,
        );
    }


    public function updateResponseStatus(int $guId, int $status)
    {
        return $this->claimRepository->updateResponseStatus(guId: $guId, status: $status);
    }


    public function getPDF(int $task_id)
    {
        $result = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . "get-pdf?id=$task_id");
        return $result->json();
    }

    public function getConclusionPDF(int $task_id)
    {
        $result = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . "get-repo-list?id=$task_id")->object();
        if (isset($result->guid) && $result->guid) {
            $file = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . "get-repo?guid=$result->guid")->object();

        } else {
            $result = (array)$result;
            if (!isset($result['status'])) {
                $count = count($result);
                if (isset($result[$count - 1]->guid) && $result[$count - 1]->guid)
                    $guid = $result[$count - 1]->guid;
                else
                    $guid = $result[0]->guid;
                $file = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . "get-repo?guid=$guid");
            } else
                return ['status' => 404];
        }
        return $file->json();
    }

    public function sendToMinstroy(ClaimSendToMinstroy $request): bool
    {
        $dataArray['SendObjectToMinstroyV2FormCompletedBuildingsRegistrationCadastral'] = [
            'comment_to_send_minstroy' => $request['comment'],
        ];

        $claimObject = $this->getClaimByGUID(guid: $request['guid']);
        $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/send-object-to-minstroy", $dataArray);

        if ($response->status() != 200) {
            return false;
        }

        $claimObject->update(
            [
                'status' => ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG,
                'end_date' => Carbon::now()
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG,
            type: LogType::TASK_HISTORY,
            date: null
        );

        return true;
    }

    public function attachObject(AttachObject $request): bool
    {
        $claimObject = $this->getClaimById(id: $request['id']);

        if(!$claimObject)
            return false;

        $claimObject->update(
            [
                'object_id' => $request['object_id']
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: ClaimStatuses::TASK_STATUS_ATTACH_OBJECT,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: 'Obyekt biriktirildi! Obyekt ID raqami:' . $request['object_id']
        );

        return true;
    }

    public function acceptTask(AcceptTask $request): bool
    {
        $dataArray['SendObjectToGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
            'comment_to_send_gasn' => $request['comment'],
        ];

        $claimObject = $this->getClaimByGUID(guid: $request['guid']);
        $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/send-object-to-gasn", $dataArray);

        if ($response->status() != 200) {
            return false;
        }

        $claimObject->update(
            [
                'status' => ClaimStatuses::TASK_STATUS_ATTACH_OBJECT,
                'end_date' => Carbon::now()
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: ClaimStatuses::TASK_STATUS_ATTACH_OBJECT,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: $request['comment']
        );

        return true;
    }

    public function getClaimFromApi($guId)
    {
        if ($guId) {
            $consolidationDb = $this->getClaimByGUID($guId);
            $response = $this->GetRequest('get-task?id=' . $guId);
            $consolidationResp = Response::where(['task_id' => $guId]);
            if ($response->status() != 200) {
                return null;
            }

            $consolidationGov = $response->object();

            if (isset($consolidationGov->task->id)) {
                $createdDate = Carbon::today();

                if ($consolidationGov->task->current_node == "inactive")
                    $createdDate = $consolidationGov->task->created_date;

                $expiryDate = $this->getExpirationDate(startDate: $createdDate, duration: 3);
                if (!$consolidationDb)
                    $this->claimRepository->createClaim($consolidationGov, $expiryDate);
                elseif ($consolidationGov->task->current_node != $consolidationDb->current_node || $consolidationGov->task->status != $consolidationDb->status_mygov) {
                    $status = ClaimStatuses::TASK_STATUS_ANOTHER;
                    if ($consolidationGov->task->current_node == "direction-statement-object")
                        $status = ClaimStatuses::TASK_STATUS_ACCEPTANCE;
                    if ($consolidationGov->task->current_node == "inactive" && $consolidationGov->task->status == "rejected")
                        $status = ClaimStatuses::TASK_STATUS_REJECTED;
                    if ($consolidationGov->task->current_node == "inactive" && $consolidationGov->task->status == "processed")
                        $status = ClaimStatuses::TASK_STATUS_CONFIRMED;
                    if ($consolidationGov->task->current_node == "inactive" && $consolidationGov->task->status == "not_active")
                        $status = ClaimStatuses::TASK_STATUS_CANCELLED;

                    $this->claimRepository->updateClaim(guId: $guId, data: [
                        'current_node' => $consolidationGov->task->current_node,
                        'status_mygov' => $consolidationGov->task->status,
                        'status' => $status
                    ]);

                    if ($status != ClaimStatuses::TASK_STATUS_ACCEPTANCE || $status != ClaimStatuses::TASK_STATUS_ANOTHER) {
                        $this->historyService->createHistory(
                            guId: $guId,
                            status: $status,
                            type: LogType::TASK_HISTORY,
                            date: $consolidationGov->task->last_update
                        );
                    }
                }
                if ($consolidationGov->task->id > 0) {
                    $consolidationResp->update([
                        'status' => ClaimStatuses::RESPONSE_WATCHED
                    ]);

                    return $consolidationGov;
                } else {
                    return null;
                }
            }

            return null;
        }

        return null;
    }



    private function GetRequest($url)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . $url);

        return $response;
    }

    private function PostRequest($url, $data)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->post($this->url . $url, $data);

        return $response;
    }

}
