<?php

namespace App\Console\Commands;

use App\Enums\DxaResponseStatusEnum;
use App\Models\District;
use App\Models\DxaResponse;
use App\Models\DxaResponseSupervisor;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class NetworkResponseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:network {task_id}';

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
        $taskId = $this->argument('task_id');
        $response = $this->fetchTaskData($taskId);
        $json = $response->json();
        $data = $this->parseResponse($response);
        $userType = $this->determineUserType($data['user_type']['real_value']);
        $date = Carbon::now();

        DB::beginTransaction();
        try {
            $dxa = $this->saveDxaResponse($taskId, $data, $userType, $response->body(), $json, $date);
            $this->saveSupervisors($data['info_supervisory']['value'], $dxa->id);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }

    protected function fetchTaskData($taskId = null)
    {
        return Http::withBasicAuth(
            'qurilish.sohasida.nazorat.inspeksiya.201122919',
            'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_'
        )->get('https://my.gov.uz/registration-start-linear-object-v1/rest-api/get-task?id=' . $taskId);
    }

    protected function parseResponse($response)
    {
        $json = $response->json();
        return $json['entities']['RegistrationStartLinearObject'];
    }

    protected function determineUserType($userTypeValue)
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

    protected function saveDxaResponse($taskId, $data, $userType, $responseBody, $json, $date)
    {
        $email = '';
        $phone = '';
        $organizationName = '';

        if ($userType == 'Yuridik shaxs')
        {
            $phone = $data['legal_phone']['real_value'];
            $organizationName = $data['legal_name']['real_value'];
        }

        if ($userType == 'Jismoniy shaxs')
        {
            $phone = $data['ind_phone']['real_value'];
            $organizationName = $data['ind_fullname']['real_value'];

        }

        $region = Region::where('soato', $data['region']['real_value'])->first();

        $district = District::where('soato', $data['district']['real_value'])->first();
        $dxa = new DxaResponse();
        $dxa->task_id = $taskId;
        $dxa->user_type = $userType;
        $dxa->dxa_response_status_id = DxaResponseStatusEnum::NEW;
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
        $dxa->object_name = $data['name_building']['real_value'];
        $dxa->deadline = $date->addDay();
        $dxa->administrative_status_id = 1;
        $dxa->object_type_id = 1;
        $dxa->region_id = $region->id;
        $dxa->district_id = $district->id;
//        $dxa->cadastral_number = $data['cadastral_number']['real_value'];
        $dxa->reestr_number = $data['reestr_number']['real_value'];
//        $dxa->tip_object = $data['tip_object']['value'];
//        $dxa->vid_object = $data['vid_object']['value'];
        $dxa->location_building = $data['location_building']['value'];
        $dxa->application_stir_pinfl = (int)$data['legal_tin']['real_value'];
        $dxa->application_name = $data['legal_name']['real_value'];
        $dxa->current_note = $json['task']['current_node'];
        $dxa->dxa_status = $json['task']['status'];
        $dxa->cost = $data['cost']['real_value'];
//        $dxa->number_protocol = $data['number_protocol']['real_value'];
//        $dxa->date_protocol = $data['date_protocol']['real_value'];
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
//        $dxa->file_energy_efficiency = $data['file_energy_efficiency']['real_value'];
        $dxa->save();

        return $dxa;
    }

    protected function saveSupervisors($supervisors, $dxaId)
    {
        foreach ($supervisors as $key => $item) {
            if ($item['role']['real_value'] ==1) {
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
            if ($item['role']['real_value'] ==2) {
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
            if ($item['role']['real_value'] ==3) {
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
}
