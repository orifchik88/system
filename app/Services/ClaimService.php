<?php

namespace App\Services;

use App\Enums\LogType;
use App\Enums\ObjectStatusEnum;
use App\Helpers\ClaimStatuses;
use App\Http\Requests\ClaimRequests\AcceptTask;
use App\Http\Requests\ClaimRequests\AttachBLockAndOrganization;
use App\Http\Requests\ClaimRequests\AttachObject;
use App\Http\Requests\ClaimRequests\ClaimSendToMinstroy;
use App\Http\Requests\ClaimRequests\ConclusionClaimByDirector;
use App\Http\Requests\ClaimRequests\ConclusionClaimByInspector;
use App\Http\Requests\ClaimRequests\ConclusionOrganization;
use App\Http\Requests\ClaimRequests\ManualConfirmDirector;
use App\Http\Requests\ClaimRequests\RejectClaimByOperator;
use App\Http\Requests\ClaimRequests\RejectFromDirector;
use App\Http\Requests\ClaimRequests\SendToDirector;
use App\Models\Article;
use App\Models\Block;
use App\Models\ClaimMonitoring;
use App\Models\ClaimOrganizationReview;
use App\Models\Response;
use App\Models\Role;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\ClaimRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class ClaimService
{
    private ClaimRepositoryInterface $claimRepository;
    private HistoryService $historyService;
    private ArticleRepositoryInterface $articleRepository;
    private string $url = 'https://my.gov.uz/completed-buildings-registration-cadastral-v2/rest-api/';

    public function __construct(
        ClaimRepositoryInterface   $claimRepository,
        ArticleRepositoryInterface $articleRepository
    )
    {
        $this->claimRepository = $claimRepository;
        $this->articleRepository = $articleRepository;
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

    public function getClaimById(int $id, ?int $role_id)
    {
        return $this->claimRepository->getClaimById(id: $id, role_id: $role_id);
    }

    public function getObjects(int $id)
    {
        return $this->claimRepository->getObjects(id: $id);
    }

    public function searchObjects($query, $filters)
    {
        return $this->claimRepository->searchObjects($query, $filters);
    }

    public function getClaimByGUID(int $guid)
    {
        return $this->claimRepository->getClaimByGUID(guid: $guid);
    }


    public function getStatistics(?int $regionId, ?int $districtId)
    {
        return $this->claimRepository->getStatistics(regionId: $regionId, districtId: $districtId);
    }

    public function getTaskStatisticsRepeated(int $region = null): array
    {
        return $this->claimRepository->getStatisticsRepeated($region);
    }

    public function getStatisticsCount(
        ?int    $regionId,
        ?int    $expired,
        ?string $dateFrom,
        ?string $dateTo,
    )
    {
        return $this->claimRepository->getStatisticsCount(
            regionId: $regionId,
            expired: $expired,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
    }

    public function getOrganizationStatistics(int $roleId, ?string $dateFrom, ?string $dateTo)
    {
        return $this->claimRepository->organizationStatistics(
            roleId: $roleId,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
    }

    public function getTaskList(
        ?int    $regionId,
        ?int    $task_id,
        ?int    $object_task_id,
        ?string $name,
        ?string $customer,
        ?string $sender,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $expired,
        ?int    $role_id
    )
    {
        return $this->claimRepository->getList(
            regionId: $regionId,
            task_id: $task_id,
            object_task_id: $object_task_id,
            name: $name,
            customer: $customer,
            sender: $sender,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            expired: $expired,
            role_id: $role_id
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

        if (env('MYGOV_MODE') == 'prod') {
            $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/send-object-to-minstroy", $dataArray);
            if ($response->status() != 200) {
                return false;
            }
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
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);

        if (!$claimObject)
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

    public function attachBlockAndOrganization(AttachBLockAndOrganization $request): bool
    {
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);
        $blocks = $request['blocks'];
        sort($blocks);
        $blocksJson = json_encode($blocks);
        $oldMonitoring = ClaimMonitoring::query()
            ->where('object_id', $claimObject->object_id)
            ->whereRaw("blocks::jsonb = '$blocksJson'")
            ->orderBy('id', 'desc')
            ->first();

        if ($claimObject->monitoring != null)
            return true;

        if (!$claimObject)
            return false;

        if ($oldMonitoring) {
            $monitoring = $this->claimRepository->createMonitoring(
                blocks: $blocks,
                organizations: $request['organizations'],
                id: $request['id'],
                object_id: $claimObject->object_id);

            $reviews = ClaimOrganizationReview::query()->where('monitoring_id', $oldMonitoring->id)->get();
            $countReviewAnswers = 0;
            $addedOrganizations = [];
            foreach ($reviews as $review) {
                $addedOrganizations[] = $review->organization_id;
                if ($review->status) {
                    $jsonTable = DB::table('claim_organization_reviews')->where('id', $review->id)->first();
                    $countReviewAnswers++;
                    ClaimOrganizationReview::query()->create(
                        [
                            'claim_id' => $request['id'],
                            'monitoring_id' => $monitoring->id,
                            'organization_id' => $review->organization_id,
                            'expiry_date' => $review->expiry_date,
                            'expired' => $review->expired,
                            'answer' => $jsonTable->answer,
                            'status' => $review->status,
                            'created_at' => $review->created_at,
                            'updated_at' => $review->updated_at,
                            'answered_at' => $review->answered_at
                        ]
                    );
                } else {
                    $this->claimRepository->createOrganizationReview(
                        claim_id: $request['id'],
                        monitoring_id: $monitoring->id,
                        organization_id: $review->organization_id,
                        expiry_date: $this->getExpirationDate(Carbon::now(), 3)
                    );
                }
            }
            if ($countReviewAnswers == count($request['organizations'])) {
                $claimObject->update(
                    [
                        'status' => ClaimStatuses::TASK_STATUS_INSPECTOR
                    ]
                );

                $this->historyService->createHistory(
                    guId: $claimObject->gu_id,
                    status: ClaimStatuses::TASK_STATUS_INSPECTOR,
                    type: LogType::TASK_HISTORY,
                    date: null
                );
            } else {
                foreach ($request['organizations'] as $organization) {
                    if (!in_array($organization, $addedOrganizations))
                        $this->claimRepository->createOrganizationReview(
                            claim_id: $request['id'],
                            monitoring_id: $monitoring->id,
                            organization_id: $organization,
                            expiry_date: $this->getExpirationDate(Carbon::now(), 3)
                        );
                }

                $claimObject->update(
                    [
                        'status' => ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION
                    ]
                );

                $this->historyService->createHistory(
                    guId: $claimObject->gu_id,
                    status: ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION,
                    type: LogType::TASK_HISTORY,
                    date: null
                );
            }
        } else {
            $monitoring = $this->claimRepository->createMonitoring(
                blocks: $blocks,
                organizations: $request['organizations'],
                id: $request['id'],
                object_id: $claimObject->object_id);

            foreach ($request['organizations'] as $organization) {
                $this->claimRepository->createOrganizationReview(
                    claim_id: $request['id'],
                    monitoring_id: $monitoring->id,
                    organization_id: $organization,
                    expiry_date: $this->getExpirationDate(Carbon::now(), 3)
                );
            }

            $claimObject->update(
                [
                    'status' => ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION
                ]
            );

            $this->historyService->createHistory(
                guId: $claimObject->gu_id,
                status: ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION,
                type: LogType::TASK_HISTORY,
                date: null
            );
        }

        return true;
    }

    public function conclusionOrganization(ConclusionOrganization $request)
    {
        $reviewObject = ClaimOrganizationReview::with('monitoring')->where('id', $request['review_id'])->first();

        if (!$reviewObject || $reviewObject->answered_at != null)
            return false;


        $apiType = match ($reviewObject->organization_id) {
            15 => 'mchs',
            16 => 'ses',
            17, 18 => 'nogiron',
            19 => 'kvartira',
        };


        $apiUrl = "update/id/" . $reviewObject->monitoring->claim->guid . "/action/conclusion-" . $apiType;

        $requestData = [
            $apiType . "_match" => $request['answer_type'],
            $apiType . "_territory" => (in_array($reviewObject->organization_id, [15, 16])) ? Auth::user()->district->name_uz : Auth::user()->region->name_uz,
            $apiType . "_name" => Auth::user()->name . ' ' . Auth::user()->surname,
            $apiType . "_conclusion_act" => $request['comment'],
            $apiType . "_datetime" => Carbon::now()
        ];

        $statusReview = $request['answer_type'] == 1;
        $this->claimRepository->updateConclusionOrganization(data: $requestData, id: $reviewObject->id, status: $statusReview);

        if ($reviewObject->organization_id != 18) {
            if (env('MYGOV_MODE') == 'prod') {
                $dataArray['Conclusion' . ucfirst($apiType) . 'V2FormCompletedBuildingsRegistrationCadastral'] = $requestData;
                $response = $this->PostRequest($apiUrl, $dataArray);

                if ($response->status() != 200) {
                    return false;
                }
            }
        }

        $role = Role::find($reviewObject->organization_id);

        $this->historyService->createHistory(
            guId: $reviewObject->monitoring->claim->guid,
            status: ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: $role->name . ' xulosa berdi! Xulosa shakllantirgan shaxs: ' . $requestData[$apiType . '_name']
        );

        $reviews = ClaimOrganizationReview::where('claim_id', $reviewObject->claim_id)->get();
        list($isFinished, $allSuccess) = $this->checkReviewCount($reviews);

        if ($isFinished) {
            if ($allSuccess) {
                $this->claimRepository->updateClaim(
                    $reviewObject->monitoring->claim->guid,
                    [
                        'status' => ClaimStatuses::TASK_STATUS_INSPECTOR
                    ]
                );

                $this->historyService->createHistory(
                    guId: $reviewObject->monitoring->claim->guid,
                    status: ClaimStatuses::TASK_STATUS_INSPECTOR,
                    type: LogType::TASK_HISTORY,
                    date: null
                );
            } else {
                $autoReject = $this->autoRejectByOrganization($reviewObject);

                if (!$autoReject)
                    return false;

                $this->historyService->createHistory(
                    guId: $reviewObject->monitoring->claim->guid,
                    status: ClaimStatuses::TASK_STATUS_ORGANIZATION_REJECTED,
                    type: LogType::TASK_HISTORY,
                    date: null
                );
            }
        }

        return $this->claimRepository->getClaimById($reviewObject->monitoring->claim->id, Auth::user()->getRoleFromToken());
    }

    private function autoRejectByOrganization($reviewObject): bool
    {
        $dataArray['SendToStepConclusionGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
            'comment_gasn' => 'Ariza tashkilotlar tomonidan ijobiy xulosa taqdim etilmaganligi sababli rad etildi.',
        ];

        $response = $this->PostRequest("update/id/" . $reviewObject->monitoring->claim->guid . "/action/send-to-step-conclusion-gasn", $dataArray);

        if ($response->status() != 200) {
            return false;
        }

        $dataArray['IssuanceExtractRejectGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
            "gasn_name_reject" => 'Shaffof qurilish milliy axborot tizimi',
            "gasn_match" => 2,
            "gasn_cause_reject" => 'Ariza tashkilotlar tomonidan ijobiy xulosa taqdim etilmaganligi sababli rad etildi.',
            "gasn_territory_reject" => $reviewObject->monitoring->claim->region->name_uz
        ];

        $response = $this->PostRequest("update/id/" . $reviewObject->monitoring->claim->guid . "/action/issuance-extract-reject-gasn", $dataArray);

        if ($response->status() != 200) {
            return false;
        }

        $reviewObject->monitoring->claim->update(
            [
                'status' => ClaimStatuses::TASK_STATUS_ORGANIZATION_REJECTED,
                'end_date' => Carbon::now()
            ]
        );

        return true;
    }

    private function checkReviewCount($reviews)
    {
        $countFinished = 0;
        $countSuccessFinished = 0;
        foreach ($reviews as $review) {
            if ($review->answered_at != null) {
                $countFinished++;
                if ($review->status)
                    $countSuccessFinished++;
            }
        }
        $isFinished = $countFinished == $reviews->count();
        $allSuccess = $countSuccessFinished == $reviews->count();
        return [$isFinished, $allSuccess];
    }

    public function acceptTask(AcceptTask $request): bool
    {
        $dataArray['SendObjectToGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
            'comment_to_send_gasn' => $request['comment'],
        ];

        $claimObject = $this->getClaimByGUID(guid: $request['guid']);

        if (env('MYGOV_MODE') == 'prod') {
            $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/send-object-to-gasn", $dataArray);
            if ($response->status() != 200) {
                return false;
            }
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

    public function manualConfirmByDirector(ManualConfirmDirector $request)
    {
        $objectModel = $this->articleRepository->findById($request['id']);
        if ($objectModel->object_status_id != ObjectStatusEnum::PROGRESS)
            return false;

        $blocks = $objectModel->blocks()->get();
        $blockError = 0;
        foreach ($blocks as $val) {
            $block = Block::find($val->id);

            if ($block->status) {
                $blockError++;
            }
        }

        if ($blockError > 0)
            return false;

        $path = $request->file->store('documents/object', 'public');

        $this->claimRepository->manualConfirmByDirector(object_id: $request['id'], comment: $request['comment'], file: $path);

        $objectModel->update(
            [
                'object_status_id' => ObjectStatusEnum::SUBMITTED,
                'closed_at' => Carbon::now()
            ]
        );

        foreach ($blocks as $val) {
            $block = Block::find($val->id);

            $block->update(
                [
                    'accepted' => true
                ]
            );
        }

        (new HistoryService('article_histories'))->createHistory(
            guId: $request['id'],
            status: ObjectStatusEnum::SUBMITTED->value,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: $request['comment']
        );

        return true;
    }

    public function sendToDirector(SendToDirector $request)
    {
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);

        $claimObject->monitoring->update(
            [
                'operator_answer' => base64_encode(gzcompress(json_encode($request['buildings']), 9))
            ]
        );

        $claimObject->update(
            [
                'status' => ClaimStatuses::TASK_STATUS_DIRECTOR,
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: ClaimStatuses::TASK_STATUS_DIRECTOR,
            type: LogType::TASK_HISTORY,
            date: null,
            comment: 'Operator boshliqga yubordi!'
        );

        return true;
    }

    public function rejectFromDirector(RejectFromDirector $request)
    {
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);

        if ($claimObject->status != ClaimStatuses::TASK_STATUS_DIRECTOR)
            return false;

        $claimObject->update(
            [
                'status' => $request['to_role'],
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: $request['to_role'],
            type: LogType::TASK_HISTORY,
            date: null,
            comment: $request['comment']
        );

        return true;
    }

    public function conclusionByDirector(ConclusionClaimByDirector $request)
    {
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);

        if ($claimObject->status != ClaimStatuses::TASK_STATUS_DIRECTOR)
            return false;

        $histories = $this->historyService->getFilteredList(guId: $claimObject->gu_id, jsonColumn: 'role', needle: 3);
        $lastInspectorConclusion = json_decode($histories[0]->content, true);

        if (env('MYGOV_MODE') == 'prod') {
            $dataArray['SendToStepConclusionGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
                'comment_gasn' => $lastInspectorConclusion['comment'],
            ];

            $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/send-to-step-conclusion-gasn", $dataArray);

            if ($response->status() != 200) {
                return false;
            }
        }

        if ($request['type'] == 15) {
            if (env('MYGOV_MODE') == 'prod') {
                $dataArray['IssuanceExtractRejectGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
                    "gasn_name_reject" => Auth::user()->name . ' ' . Auth::user()->surname,
                    "gasn_match" => 2,
                    "gasn_cause_reject" => $request['comment'],
                    "gasn_territory_reject" => Auth::user()->region->name_uz
                ];

                $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/issuance-extract-reject-gasn", $dataArray);

                if ($response->status() != 200) {
                    return false;
                }
            }

            $claimObject->update(
                [
                    'status' => ClaimStatuses::TASK_STATUS_REJECTED,
                    'end_date' => Carbon::now()
                ]
            );

            $this->historyService->createHistory(
                guId: $claimObject->gu_id,
                status: ClaimStatuses::TASK_STATUS_REJECTED,
                type: LogType::TASK_HISTORY,
                date: null,
                comment: $request['comment']
            );
        } else {
            $operatorBlocks = $claimObject->monitoring->operator_field;
            if (!$operatorBlocks)
                return false;
            $tableHtml = '<table style="border-collapse: collapse; width: 100%;">
                              <thead>
                                <tr>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Бино ва иншоотнинг номи</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Кадастр рақами</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Бинолар сони</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Умумий майдон (ташқи) (м²)</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Умумий фойдаланиш майдони (m²)</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Яшаш майдони (м²)</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">Қурилиш ости майдони (м²)</th>
                                </tr>
                              </thead>
                              <tbody>
                                {blocks}
                              </tbody>
                              <tfoot>
                                {footer}
                              </tfoot>
                            </table>';
            $totalArr = [
                'building_number' => 0,
                'total_area' => 0,
                'total_use_area' => 0,
                'living_area' => 0,
                'area' => 0,
            ];
            $tableTr = '';
            foreach ($operatorBlocks as $operatorBlock) {
                $totalArr['building_number'] += $operatorBlock['building_number'];
                $totalArr['total_area'] += $operatorBlock['total_area'];
                $totalArr['total_use_area'] += $operatorBlock['total_use_area'];
                $totalArr['living_area'] += $operatorBlock['living_area'];
                $totalArr['area'] += $operatorBlock['area'];

                $tableTr .= '<tr>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;"> ' . $operatorBlock['name'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['cadaster'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['building_number'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['total_area'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['total_use_area'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['living_area'] . '</td>
                                  <td style="border: 1px solid black; padding: 5px; text-align: center;">' . $operatorBlock['area'] . '</td>
                                </tr>';
            }

            $footer = '<tr>
                                  <th style="border: 1px solid black; padding: 5px;" colspan="2">Жами:</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">' . $totalArr['building_number'] . '</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">' . $totalArr['total_area'] . '</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">' . $totalArr['total_use_area'] . '</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">' . $totalArr['living_area'] . '</th>
                                  <th style="border: 1px solid black; padding: 5px; text-align: center;">' . $totalArr['area'] . '</th>
                                </tr>';
            $tableHtml = str_replace('{blocks}', $tableTr, $tableHtml);
            $tableHtml = str_replace('{footer}', $footer, $tableHtml);
            if (env('MYGOV_MODE') == 'prod') {
                $dataArray['ConclusionGasnV2FormCompletedBuildingsRegistrationCadastral'] = [
                    "gasn_name" => Auth::user()->name . ' ' . Auth::user()->surname,
                    "gasn_match" => 1,
                    "gasn_cause" => $request['comment'],
                    "gasn_territory" => Auth::user()->region->name_uz,
                    "date_issue_act_gasn" => Carbon::now(),
                    "object_project_gasn" => [
                        "target" => "file",
                        "ext" => "jpg",
                        "file" => "iVBORw0KGgoAAAANSUhEUgAAAPgAAADLCAMAAAB04a46AAAAKlBMVEX///8AAABMTEw/Pz+cnJz19fU6OjpDQ0NLS0tISEhycnIMDAyampqsrKz4G7DdAAABLUlEQVR4nO3UyRHCQAwAQbABL1f+6QIPKEfAPqYnAnVJpcNBtZ7bGEur6+0DPx17jSp8+cLXUNt9B5/3YSa07uDn2cP8sws4eCNw8Ejg4JHAwSOBg0cCB48EDh4JHDwSOHgkcPBI4OCRwMEjgYNHAgePBA4eCRw8Ejh4JHDwSODgkcDBI4GDRwIHjwQOHgkcPBI4eCRw8Ejg4JHAwSOBg0cCB48EDh4JHDwSOHgkcPBI4OCRwMEjgYNHAgePBA4eCRw8Ejh4JHDwSODgkcDBI4GDRwIHjwQOHgkcPBI4eCRw8Ejg4JHAwSOBg0cCB48EDh4JHDwSOHgkcPBI4OCRwMEjgYNHAgePBF6FP97w2bP8tXW38TXUdt/Ba/1Ovdb4wJ/bMpZW19u8D6NZvQAHUx5B5LstjAAAAABJRU5ErkJggg=="
                    ],
                    "address_object_gasn" => $claimObject->object->region->name_uz . ', ' . $claimObject->object->district->name_uz . ', ' . $claimObject->object->location_building,
                    "buildings_title_documents_gasn" => $tableHtml
                ];

                $response = $this->PostRequest("update/id/" . $claimObject->gu_id . "/action/conclusion-gasn", $dataArray);

                if ($response->status() != 200) {
                    return false;
                }
            }

            $claimObject->update(
                [
                    'status' => ClaimStatuses::TASK_STATUS_CONFIRMED,
                    'end_date' => Carbon::now()
                ]
            );

            foreach ($claimObject->blocks as $block) {
                $blockId = $block['id'];
                $blockModel = Block::find($blockId);

                $blockModel->update(
                    [
                        'accepted' => true
                    ]
                );
            }

            $checkBLocks = Block::query()->where('article_id', $claimObject->object_id)->get();
            $countBlocks = 0;
            foreach ($checkBLocks as $checkBLock) {
                if ($checkBLock->accepted)
                    $countBlocks++;
            }

            if ($countBlocks == $checkBLocks->count()) {
                $object = $this->articleRepository->findById($claimObject->object_id);
                $object->update(
                    [
                        'object_status_id' => ObjectStatusEnum::SUBMITTED,
                        'closed_at' => Carbon::now()
                    ]
                );
            }

            (new HistoryService('article_histories'))->createHistory(
                guId: $claimObject->object_id,
                status: ObjectStatusEnum::SUBMITTED->value,
                type: LogType::TASK_HISTORY,
                date: null,
                comment: $request['comment']
            );

            $this->historyService->createHistory(
                guId: $claimObject->gu_id,
                status: ClaimStatuses::TASK_STATUS_CONFIRMED,
                type: LogType::TASK_HISTORY,
                date: null,
                comment: $request['comment']
            );
        }

        $claimObject->monitoring->update(
            [
                'director_answer' => $request['type']
            ]
        );

        return true;
    }

    public function conclusionByInspector(ConclusionClaimByInspector $request)
    {
        $claimObject = $this->getClaimById(id: $request['id'], role_id: null);

        if ($claimObject->status != ClaimStatuses::TASK_STATUS_INSPECTOR)
            return false;

        $claimObject->monitoring->update(
            [
                'inspector_answer' => $request['type'],
            ]
        );

        $claimObject->update(
            [
                'status' => ClaimStatuses::TASK_STATUS_OPERATOR,
            ]
        );

        $this->historyService->createHistory(
            guId: $claimObject->gu_id,
            status: ClaimStatuses::TASK_STATUS_OPERATOR,
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
                    if ($consolidationGov->task->current_node == "direction-statement-object") {
                        $status = ClaimStatuses::TASK_STATUS_ACCEPTANCE;
                        if ($consolidationDb->object_id != null)
                            $status = ClaimStatuses::TASK_STATUS_ATTACH_OBJECT;
                    }
                    if ($consolidationGov->task->current_node == "answer-other-institutions") {
                        $status = ClaimStatuses::TASK_STATUS_SENT_ORGANIZATION;
                        $reviews = ClaimOrganizationReview::where('claim_id', $consolidationDb->id)->get();
                        list($isFinished, $allSuccess) = $this->checkReviewCount($reviews);

                        if ($allSuccess) {
                            $status = ClaimStatuses::TASK_STATUS_INSPECTOR;
                        }
                    }
                    if ($consolidationGov->task->current_node == "conclusion-minstroy")
                        $status = ClaimStatuses::TASK_STATUS_SENT_ANOTHER_ORG;
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

    private
    function GetRequest($url)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->get($this->url . $url);

        return $response;
    }

    private
    function PostRequest($url, $data)
    {
        $response = Http::withBasicAuth(config('app.mygov.login'), config('app.mygov.password'))->post($this->url . $url, $data);

        return $response;
    }

}
