<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\DifficultyCategoryEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\ObjectStatusEnum;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\Costumer;
use App\Models\DxaResponse;
use App\Models\FundingSource;
use App\Models\ObjectSector;
use App\Models\ObjectType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ArticleService
{
    protected ObjectDto $objectDto;

    public function __construct(protected Article       $article,
                                protected ObjectType    $objectType,
                                protected FundingSource $fundingSource,
                                protected ObjectSector  $objectSector,
                                protected DxaResponse   $dxaResponse)
    {
    }

    public function setObjectDto(ObjectDto $objectDto): void
    {
        $this->objectDto = $objectDto;
    }


    public function getAllTypes(): object
    {
        return $this->objectType->all();
    }

    public function getType($id)
    {
        return $this->objectType->findOrFail($id);
    }

    public function getObjectSectors($id): object
    {
        $foundingSource = $this->fundingSource->find($id);
        if (empty($foundingSource)) {
            throw  new NotFoundException('Object sectors not found');
        }
        return $foundingSource->objectSectors;
    }

    public function getAllFundingSources(): object
    {
        return $this->fundingSource->all();
    }

    public function createObject()
    {

        DB::beginTransaction();
        try {
            $response = $this->dxaResponse->find($this->objectDto->responseId);
            $response->update([
                'is_accepted' => true,
                'dxa_response_status_id' => DxaResponseStatusEnum::ACCEPTED
            ]);

            $tinOrPinfl = $response->pinfl ?? $response->application_stir_number;

            $article = new Article();
            $article->name = $response->object_name;
            $article->region_id = $response->region_id;
            $article->district_id = $response->district_id;
            $article->object_status_id = ObjectStatusEnum::NEW;
            $article->address = $response->address;
//            $article->location_building = $response->location_building;
            $article->cadastral_number = $response->cadastral_number;
            $article->name_expertise = $response->name_expertise;
            $article->difficulty_category_id = DifficultyCategoryEnum::fromString($response->construction_works)->value;
            $article->construction_cost = $response->cost;
//        $article->object_images = $response->district_id;
//        $article->object_type_id = $response->district_id;
//        $article->property_type = $response->property_type;
            $article->architectural_number_date_protocol = null;
            $article->parallel_designobjc = null;
            $article->objects_stateprog = null;
            $article->name_date_posopin = null;
            $article->name_date_licontr = null;
            $article->appearance_type_id = 1;
            $article->is_accepted = true;
            $article->organization_projects = $response->organization_projects;
            $article->specialists_certificates = $response->specialists_certificates;
            $article->contract_file = $response->contract_file;
            $article->confirming_laboratory = $response->confirming_laboratory;
            $article->file_energy_efficiency = $response->file_energy_efficiency;
            $article->legal_opf = $response->legal_opf;
            $article->lat = $response->lat;
            $article->long = $response->long;
//        $article->authority_id = $response->property_type;
//        $article->lat_long_status = $response->property_type;
            $article->dxa_response_id = $response->id;
//        $article->company_id = $response->property_type;
//        $article->applicant_id = $response->property_type;
            $article->price_supervision_service = price_supervision($response->cost);
            $article->task_id = $response->task_id;
//        $article->costumer_id = $response->property_type;
            $article->number_protocol = $response->number_protocol;
            $article->positive_opinion_number = $response->positive_opinion_number;
//        $article->positive_opinion_date = $response->property_type;
            $article->date_protocol = $response->date_protocol;
//        $article->object_specific_id ; // tarmoqli yoki bino
            $article->funding_source_id = $this->objectDto->fundingSourceId;
//        $article->re_formalized_object = $response->property_type;
            $article->paid = 0; // tolangan summa
            $article->payment_deadline = Carbon::now(); // tolov qilish sanasi
//        $article->closed_at = $response->property_type;
            $article->object_sector_id = $this->objectDto->objectSectorId;
//        $article->object_category_id = $response->property_type;
            $article->deadline = null;
            $article->update_by = null;
            $article->block_status_counter = null;
            $article->costumer_cer_num = null;
            $article->planned_object_id = null;
            $article->min_ekonom_id = null;
            $article->gnk_id = null;
//        $article->t_is_changed = ;
            $article->reestr_number = $response->reestr_number;
            $article->save();

            $author = Role::whereHas('permissions', function ($query) {
                $query->where('name', 'is_author');
            })->first();

            $technic = Role::whereHas('permissions', function ($query) {
                $query->where('name', 'is_technical');
            })->first();

            $designer = Role::whereHas('permissions', function ($query) {
                $query->where('name', 'is_designer');
            })->first();
            $inspector = Role::whereHas('permissions', function ($query) {
                $query->where('name', 'is_inspector');
            })->first();

            foreach ($response->supervisors as $supervisor) {
                $fish = $this->generateFish($supervisor->fish);
                if ($supervisor->type == 1) {
                    $user = User::where('passport_number', $supervisor->passport_number)->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $fish[1],
                            'surname' => $fish[0],
                            'middle_name' => $fish[2],
                            'phone' => $supervisor->phone_number,
                            'email' => $supervisor->email,
                            'login' => $supervisor->passport_number,
                            'password' => bcrypt($supervisor->passport_number),
                            'passport_number' => $supervisor->passport_number,
                            'user_status_id' => 6,
                            'pinfl' => $supervisor->pinfl,
                            'name_graduate_study' => $supervisor->name_graduate_study,
                            'diplom_number' => $supervisor->diplom_number,
                            'specialization' => $supervisor->specialization,
                            'date_issue_diploma' => $supervisor->diplom_date,
                        ]);
                        $user->assignRole($author->id);
                        $article->users()->attach($user->id, ['role_id' => $author->id, 'organization_id' => 1]);
                    }

                }
                if ($supervisor->type == 2) {
                    $user = User::where('passport_number', $supervisor->passport_number)->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $fish[1],
                            'surname' => $fish[0],
                            'middle_name' => $fish[2],
                            'email' => $supervisor->email,
                            'phone' => $supervisor->phone_number,
                            'login' => $supervisor->passport_number,
                            'password' => bcrypt($supervisor->passport_number),
                            'passport_number' => $supervisor->passport_number,
                            'user_status_id' => 6,
                            'pinfl' => $supervisor->pinfl,
                            'name_graduate_study' => $supervisor->name_graduate_study,
                            'diplom_number' => $supervisor->diplom_number,
                            'specialization' => $supervisor->specialization,
                            'date_issue_diploma' => $supervisor->diplom_date,
                        ]);
                        $user->assignRole($designer->id);
                        $article->users()->attach($user->id, ['role_id' => $designer->id, 'organization_id' => 1]);
                    }

                }

                if ($supervisor->type == 3) {
                    $user = User::where('passport_number', $supervisor->passport_number)->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $fish[1],
                            'surname' => $fish[0],
                            'middle_name' => $fish[2],
                            'email' => $supervisor->email,
                            'phone' => $supervisor->phone_number,
                            'login' => $supervisor->passport_number,
                            'password' => bcrypt($supervisor->passport_number),
                            'passport_number' => $supervisor->passport_number,
                            'user_status_id' => 6,
                            'pinfl' => $supervisor->pinfl,
                            'name_graduate_study' => $supervisor->name_graduate_study,
                            'diplom_number' => $supervisor->diplom_number,
                            'specialization' => $supervisor->specialization,
                            'date_issue_diploma' => $supervisor->diplom_date,
                        ]);
                        $user->assignRole($technic->id);
                        $article->users()->attach($user->id, ['role_id' => $technic->id, 'organization_id' => 1]);
                    }
                }
            }

            $article->users()->attach($response->inspector_id, ['role_id' => $inspector->id, 'organization_id' => 1]);


            DB::commit();
            return $article;

        } catch (Exception $exception) {
            DB::rollBack();
            throw new NotFoundException($exception->getMessage(), $exception->getCode());
        }


//        $fundingSource = $this->fundingSource->find($this->objectDto->fundingSourceId);
//        $objectType = $this->objectSector->find($this->objectDto->objectSectorId);

    }


    private function acceptResponse($taskId, $amount)
    {
        return Http::withBasicAuth(
            'qurilish.sohasida.nazorat.inspeksiya.201122919',
            'Cx8]^]-Gk*mZK@.,S=c.g65>%[$TNRV75bYX<v+_'
        )->post('https://my.gov.uz/notice-beginning-construction-works-v4/rest-api/update/id/' . $taskId . '/action/issue-amount', [
            "IssueAmountV4FormNoticeBeginningConstructionWorks" => [
                "requisites" => "example",
                "loacation_rep" => "example",
                "name_rep" => "example",
                "amount" => $amount
            ]
        ]);
    }

    private function generateFish($name): array
    {
        $parts = explode(' ', $name);
        $array = [
            $parts[0],  // lastname
            $parts[1],  // name
            implode(' ', array_slice($parts, 2))  // middle name
        ];

        return $array;
    }

}
