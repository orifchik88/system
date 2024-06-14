<?php

namespace App\Console\Commands;

use App\Models\DxaResponse;
use App\Models\DxaResponseSupervisor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ResponseCreated extends Command
{
    private $task_id;
    const URL = "https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/get-task?id=";
    const USERNAME = "qurilish.sohasida.nazorat.inspeksiya.201122919";
    const PASSWORD = 'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */


    protected $signature = 'app:response {task_id}';

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
//        DxaResponse::query()->truncate();
//        die();
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
        )->get('https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/get-task?id=' . $taskId);
    }

    protected function parseResponse($response)
    {
        $json = $response->json();
        return $json['entities']['NoticeBeginningConstructionWorks'];
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

        if ($userType == 'Yuridik shaxs')
        {
            $email = $data['legal_entity_email']['real_value'];
            $phone = $data['legal_entity_phone_number']['real_value'];
        }

        if ($userType == 'Jismoniy shaxs')
        {
            $email = $data['email']['real_value'];
            $phone = $data['phone']['real_value'];
        }

        $dxa = new DxaResponse();
        $dxa->task_id = $taskId;
        $dxa->task = $responseBody;
        $dxa->user_type = $userType;
        $dxa->dxa_response_statuses_id = 3;
        $dxa->email = $email;
        $dxa->full_name = $data['full_name']['real_value'];
        $dxa->pinfl = $data['ind_pinfl']['real_value'];
        $dxa->passport = $data['passport_number']['real_value'];
        $dxa->permit_address = $data['permit_address']['real_value'];
        $dxa->address = $data['legal_entity_address']['real_value'];
        $dxa->organization_name = $data['legal_entity_name']['real_value'];
        $dxa->legal_opf = $data['legal_opf']['real_value'];
        $dxa->phone = $phone;
        $dxa->object_name = $data['name_building']['real_value'];
        $dxa->deadline = $date->addDay();
        $dxa->administrative_statuses_id = 1;
        $dxa->region_id = $data['region_id']['real_value'];
        $dxa->district_id = $data['district_id']['real_value'];
        $dxa->cadastral_number = $data['cadastral_number']['real_value'];
        $dxa->reestr_number = $data['reestr_number']['real_value'];
        $dxa->tip_object = $data['tip_object']['value'];
        $dxa->vid_object = $data['vid_object']['value'];
        $dxa->location_building = $data['location_building']['value'];
        $dxa->application_stir_pinfl = $data['legal_entity_tin']['real_value'];
        $dxa->application_name = $data['legal_entity_name']['real_value'];
        $dxa->current_note = $json['task']['current_node'];
        $dxa->dxa_status = $json['task']['status'];
        $dxa->cost = $data['cost']['real_value'];
        $dxa->number_protocol = $data['number_protocol']['real_value'];
        $dxa->date_protocol = $data['date_protocol']['real_value'];
        $dxa->category_object_dictionary = $data['category_object_dictionary']['real_value'];
        $dxa->construction_works = $data['category_object_dictionary']['value'];
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
        $dxa->file_energy_efficiency = $data['file_energy_efficiency']['real_value'];
        $dxa->save();

        return $dxa;
    }

    protected function saveSupervisors($supervisors, $dxaId)
    {
        foreach ($supervisors as $key => $item) {
            $dxaResSupervisor = new DxaResponseSupervisor();
            $dxaResSupervisor->dxa_response_id = $dxaId;
            $dxaResSupervisor->type = $key;
            $dxaResSupervisor->role = $item['role']['value'];
            $dxaResSupervisor->organization_name = $item['name_org']['value'];
            $dxaResSupervisor->identification_number = (int)$item['tin_org']['real_value'];
            $dxaResSupervisor->pinfl = (int)$item['head_pinfl']['real_value'];
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
        }
    }
}
