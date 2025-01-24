<?php

namespace App\Services;

use App\Enums\DxaResponseStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Article;
use App\Models\District;
use App\Models\DxaResponse;
use App\Models\DxaResponseSupervisor;
use App\Models\Region;
use App\Models\Rekvizit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DxaLinearResponseService
{
    const USERNAME = 'qurilish.sohasida.nazorat.inspeksiya.201122919';
    const PASSWORD = 'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_';
    const URL = 'https://my.gov.uz/registration-start-linear-object-v1/rest-api/get-task?id=';


    public function fetchTaskData($taskId = null)
    {
        return Http::withBasicAuth(
            self::USERNAME,
            self::PASSWORD,
        )->get(self::URL . $taskId);
    }

    public function parseResponse($response)
    {
        $json = $response->json();
        return $json['entities']['RegistrationStartLinearObject'];
    }

    public function determineUserType($userTypeValue)
    {
        switch ($userTypeValue) {
            case 'I':
                return 'Jismoniy shaxs';
            case 'J':
                return 'Yuridik shaxs';
            default:
                return '';
        }
    }

    public function saveDxaResponse($taskId, $data, $userType, $json)
    {
        $email = '';
        $phone = '';
        $organizationName = '';
        $status = null;

        if ($userType == 'Yuridik shaxs') {
            $phone = $data['legal_phone']['real_value'];
            $organizationName = $data['legal_name']['real_value'];
        }

        if ($userType == 'Jismoniy shaxs') {
            $phone = $data['ind_phone']['real_value'];
            $organizationName = $data['ind_fullname']['real_value'];
        }

        if ($json['task']['current_node'] == 'inactive') {
            if ($json['task']['status'] == 'processed') {
                $status = DxaResponseStatusEnum::ACCEPTED;
            }
            if ($json['task']['status'] == 'rejected') {
                $status = DxaResponseStatusEnum::REJECTED;
            }
        }else{
            $status = DxaResponseStatusEnum::NEW;
        }

        $region = Region::where('soato', $data['region']['real_value'])->first();
        $oldTaskId = !empty($data['task_number']['real_value']) ? $data['task_number']['real_value'] : null;


        $district = District::where('soato', $data['district']['real_value'])->first();
        $dxa = new DxaResponse();
        $dxa->task_id = $taskId;
        $dxa->old_task_id = $oldTaskId;
        $dxa->notification_type = $data['notification_type']['real_value'];
        $dxa->user_type = $userType;
        $dxa->dxa_response_status_id = $status;
        $dxa->email = $email;
        $dxa->full_name = $data['ind_fullname']['real_value'];
        $dxa->name_expertise = $data['name_expertise']['real_value'];
        $dxa->pinfl = $data['ind_pinfl']['real_value'];
        $dxa->passport = $data['ind_passport']['real_value'];
        $dxa->permit_address = $data['ind_address']['real_value'];
        $dxa->address = $data['legal_address']['real_value'];
        $dxa->organization_name = $organizationName;
        $dxa->legal_opf = $data['legal_kopf']['real_value'];
        $dxa->phone = $phone;
        $dxa->object_name = $data['object_name']['real_value'];
        $dxa->deadline = Carbon::now()->addDay();
        $dxa->administrative_status_id = 1;
        $dxa->object_type_id = 1;
        $dxa->region_id = $region->id;
        $dxa->district_id = $district->id;
        $dxa->reestr_number = $data['positive_opinion_number']['real_value'];
        $dxa->location_building = $data['location_building']['value'];
        $dxa->linear_type = $data['line_feature_type']['value'];
        $dxa->application_stir_pinfl = (int)$data['legal_tin']['real_value'];
        $dxa->application_name = $data['legal_name']['real_value'];
        $dxa->current_note = $json['task']['current_node'];
        $dxa->dxa_status = $json['task']['status'];
        $dxa->cost = $data['cost']['real_value'];
        $dxa->category_object_dictionary = $data['object_category']['value'];
        $dxa->construction_works = $data['construction_works']['value'];
        $dxa->object_parallel_design_number = $data['object_parallel_design_number']['real_value'];
        $dxa->object_parallel_design_date = $data['object_parallel_design_date']['real_value'];
        $dxa->object_state_program_number = $data['object_state_program_number']['real_value'];
        $dxa->object_state_program_date = $data['object_state_program_date']['real_value'];
        $dxa->name_expertise = $data['name_expertise']['real_value'];
        $dxa->positive_opinion_number = $data['positive_opinion_number']['real_value'];
        $dxa->contractor_license_number = $data['contractor_license_number']['real_value'];
        $dxa->contractor_license_date = $data['contractor_license_date']['real_value'];
        $dxa->industrial_security_number = $data['industrial_security_number']['real_value'];
        $dxa->industrial_security_date = $data['industrial_security_date']['real_value'];
        $dxa->confirming_laboratory = $data['confirming_laboratory']['real_value'];
        $dxa->specialists_certificates = $data['specialists_certificates']['real_value'];
        $dxa->contract_file = $data['contract_file']['real_value'];
        $dxa->organization_projects = $data['organization_projects']['real_value'];
        $dxa->created_at = $json['task']['created_date'];
        $dxa->save();
        $this->saveSupervisors($data, $dxa->id, $userType);
        $this->updateObject($dxa, $json);
        if ($dxa->notification_type == 2) {
            $this->saveRekvizit($dxa);
        }
        return $dxa;
    }

    private function updateObject($dxa, $json)
    {
        $object = Article::query()->where('task_id', $dxa->task_id)->first();
        if ($object) {
            $object->update([
                'object_type_id' => 1,
//                'linear_type' => $dxa->linear_type,
                'reestr_number' => $dxa->reestr_number,
                'created_at' => $json['task']['last_update']
            ]);
        }
    }

    private function saveRekvizit($response)
    {

        $rekvizit = Rekvizit::query()->where('region_id', $response->region_id)->first();
        $response->update([
            'rekvizit_id' => $rekvizit->id,
            'price_supervision_service' => price_supervision((int)$response->cost)
        ]);
    }

    public function saveExpertise($dxa)
    {
        try {
            if ($dxa->notification_type == 2) {
                $response = DxaResponse::query()->where('task_id', $dxa->old_task_id)->first();
                if ($response)
                {
                    $reestrNumber = isset($dxa->reestr_number) ? $response->reestr_number : $dxa->reestr_number;
                    $article = Article::query()->where('task_id', $dxa->old_task_id)->first();
                    $dxa->update([
                        'reestr_number' => $reestrNumber,
                        'sphere_id' => $response->sphere_id,
                        'monitoring_object_id' => $response->monitoring_object_id,
                        'end_term_work' => $response->end_term_work,
                        'administrative_status_id' => $response->administrative_status_id,
                        'inspector_id' => $article ? $article?->users()->where('role_id', UserRoleEnum::INSPECTOR->value)?->id : null,
                    ]);
                }

            } else {
                $reestrNumber = $dxa->reestr_number;
            }
            $data = getData(config('app.gasn.tender'), $reestrNumber);
            $dxa->update([
                'funding_source_id' => $data['data']['result']['data']['finance_source'],
                'program_id' => $data['data']['result']['data']['project_type_id'],
               // 'sphere_id' => $data['data']['result']['data']['object_type_id'],
            ]);
        } catch (\Exception $exception) {
            Log::error('Expertise saqlashda xatolik: ' . $exception->getMessage());
        }

    }

    private function saveSupervisors($data, $dxaId, $userType)
    {

        foreach ($data['info_supervisory']['value'] as $key => $item) {
            if ($item['role']['real_value'] == 1) {
                $dxaResSupervisor = new DxaResponseSupervisor();
                $dxaResSupervisor->dxa_response_id = $dxaId;
                $dxaResSupervisor->type = $item['role']['real_value'];
                $dxaResSupervisor->role = $item['role']['value'];
                $dxaResSupervisor->role_id = 6;
                $dxaResSupervisor->organization_name = $item['name_org']['value'];
                $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->stir_or_pinfl = (int)$item['head_pinfl']['real_value'];
                $dxaResSupervisor->fish = $item['head_fname']['real_value'];
                $dxaResSupervisor->passport_number = $item['passpnm_data_series']['real_value'];
                $dxaResSupervisor->name_graduate_study = $item['name_graduate_study']['real_value'];
                $dxaResSupervisor->specialization = $item['specialization']['real_value'];
                $dxaResSupervisor->diplom_number = $item['diploma_number']['real_value'];
                $dxaResSupervisor->diplom_date = $item['date_issue_diploma']['real_value'];
                $dxaResSupervisor->sertificate_number = $item['certificate_courses']['real_value'];
                $dxaResSupervisor->phone_number = $item['phone_number']['real_value'];
                $dxaResSupervisor->comment = $item['comment']['real_value'];
                $dxaResSupervisor->save();

                if ($userType == 'Jismoniy shaxs') {
                    $dxaResSupervisor = new DxaResponseSupervisor();
                    $dxaResSupervisor->dxa_response_id = $dxaId;
                    $dxaResSupervisor->type = $item['role']['real_value'];
                    $dxaResSupervisor->role = $item['role']['value'];
                    $dxaResSupervisor->role_id = 8;
                    $dxaResSupervisor->organization_name = $data['ind_fullname']['value'];
                    $dxaResSupervisor->fish = $data['ind_fullname']['value'];
                    $dxaResSupervisor->passport_number = $data['ind_passport']['real_value'];
                    $dxaResSupervisor->identification_number = (int)$data['ind_pinfl']['real_value'];
                    $dxaResSupervisor->stir_or_pinfl = (int)$data['ind_pinfl']['real_value'];
                    $dxaResSupervisor->comment = $item['comment']['real_value'];
                    $dxaResSupervisor->save();

                } else {
                    $dxaResSupervisor = new DxaResponseSupervisor();
                    $dxaResSupervisor->dxa_response_id = $dxaId;
                    $dxaResSupervisor->type = $item['role']['real_value'];
                    $dxaResSupervisor->role = $item['role']['value'];
                    $dxaResSupervisor->role_id = 8;
                    $dxaResSupervisor->organization_name = $item['name_org']['value'];
                    $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                    $dxaResSupervisor->stir_or_pinfl = (int)$item['tin_org']['real_value'];
                    $dxaResSupervisor->comment = $item['comment']['real_value'];
                    $dxaResSupervisor->save();
                }
            }
            if ($item['role']['real_value'] == 2) {
                $dxaResSupervisor = new DxaResponseSupervisor();
                $dxaResSupervisor->dxa_response_id = $dxaId;
                $dxaResSupervisor->type = $item['role']['real_value'];
                $dxaResSupervisor->role = $item['role']['value'];
                $dxaResSupervisor->role_id = 7;
                $dxaResSupervisor->organization_name = $item['name_org']['value'];
                $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->stir_or_pinfl = (int)$item['head_pinfl']['real_value'];
                $dxaResSupervisor->fish = $item['head_fname']['real_value'];
                $dxaResSupervisor->passport_number = $item['passpnm_data_series']['real_value'];
                $dxaResSupervisor->name_graduate_study = $item['name_graduate_study']['real_value'];
                $dxaResSupervisor->specialization = $item['specialization']['real_value'];
                $dxaResSupervisor->diplom_number = $item['diploma_number']['real_value'];
                $dxaResSupervisor->diplom_date = $item['date_issue_diploma']['real_value'];
                $dxaResSupervisor->sertificate_number = $item['certificate_courses']['real_value'];
                $dxaResSupervisor->phone_number = $item['phone_number']['real_value'];
                $dxaResSupervisor->comment = $item['comment']['real_value'];
                $dxaResSupervisor->save();

                $dxaResSupervisor = new DxaResponseSupervisor();
                $dxaResSupervisor->dxa_response_id = $dxaId;
                $dxaResSupervisor->type = $item['role']['real_value'];
                $dxaResSupervisor->role_id = 9;
                $dxaResSupervisor->role = $item['role']['value'];
                $dxaResSupervisor->organization_name = $item['name_org']['value'];
                $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->stir_or_pinfl = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->comment = $item['comment']['real_value'];
                $dxaResSupervisor->save();


            }
            if ($item['role']['real_value'] == 3) {
                $dxaResSupervisor = new DxaResponseSupervisor();
                $dxaResSupervisor->dxa_response_id = $dxaId;
                $dxaResSupervisor->type = $item['role']['real_value'];
                $dxaResSupervisor->role = $item['role']['value'];
                $dxaResSupervisor->role_id = 5;
                $dxaResSupervisor->organization_name = $item['name_org']['value'];
                $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->stir_or_pinfl = (int)$item['head_pinfl']['real_value'];
                $dxaResSupervisor->fish = $item['head_fname']['real_value'];
                $dxaResSupervisor->passport_number = $item['passpnm_data_series']['real_value'];
                $dxaResSupervisor->name_graduate_study = $item['name_graduate_study']['real_value'];
                $dxaResSupervisor->specialization = $item['specialization']['real_value'];
                $dxaResSupervisor->diplom_number = $item['diploma_number']['real_value'];
                $dxaResSupervisor->diplom_date = $item['date_issue_diploma']['real_value'];
                $dxaResSupervisor->sertificate_number = $item['certificate_courses']['real_value'];
                $dxaResSupervisor->phone_number = $item['phone_number']['real_value'];
                $dxaResSupervisor->comment = $item['comment']['real_value'];
                $dxaResSupervisor->save();

                $dxaResSupervisor = new DxaResponseSupervisor();
                $dxaResSupervisor->dxa_response_id = $dxaId;
                $dxaResSupervisor->type = $item['role']['real_value'];
                $dxaResSupervisor->role_id = 10;
                $dxaResSupervisor->role = $item['role']['value'];
                $dxaResSupervisor->organization_name = $item['name_org']['value'];
                $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->stir_or_pinfl = (int)$item['tin_org']['real_value'];
                $dxaResSupervisor->comment = $item['comment']['real_value'];
                $dxaResSupervisor->save();

            }

        }
    }

    public function sendMyGov($response)
    {
        try {
                $authUsername = config('app.mygov.login');
                $authPassword = config('app.mygov.password');

                $apiUrl = config('app.mygov.linear') . '/update/id/' . $response->task_id . '/action/accept-consideration';
                $formName = 'AcceptConsiderationFormRegistrationStartLinearObject';

                return Http::withBasicAuth($authUsername, $authPassword)
                    ->post($apiUrl, [
                        $formName => [
                            "notice" => " Ariza ko'rib chiqishga qabul qilindi. nazorat.mc.uz"
                        ]
                    ]);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }

}
