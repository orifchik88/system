<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\DifficultyCategoryEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserStatusEnum;
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
use Illuminate\Support\Str;
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
                'confirmed_at' => now(),
                'dxa_response_status_id' => DxaResponseStatusEnum::ACCEPTED
            ]);


            $article = new Article();
            $article->name = Str::limit($response->object_name, 40);
            $article->region_id = $response->region_id;
            $article->district_id = $response->district_id;
            $article->object_status_id = ObjectStatusEnum::NEW;
            $article->object_type_id = $response->object_type_id;
            $article->address = $response->address;
            $article->cadastral_number = $response->cadastral_number;
            $article->name_expertise = $response->name_expertise;
            $article->difficulty_category_id = DifficultyCategoryEnum::fromString($response->category_object_dictionary);
            $article->construction_cost = $response->cost;
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
            $article->dxa_response_id = $response->id;
            $article->price_supervision_service = price_supervision($response->cost);
            $article->task_id = $response->task_id;
            $article->number_protocol = $response->number_protocol;
            $article->positive_opinion_number = $response->positive_opinion_number;
            $article->date_protocol = $response->date_protocol;
            $article->funding_source_id = $response->funding_source_id;
            $article->paid = 0;
            $article->payment_deadline = Carbon::now();
            $article->deadline = $response->end_term_work;
            $article->update_by = null;
            $article->block_status_counter = null;
            $article->costumer_cer_num = null;
            $article->planned_object_id = null;
            $article->min_ekonom_id = null;
            $article->gnk_id = $response->gnk_id;
            $article->reestr_number = (int)$response->reestr_number;
            $article->save();

            foreach ($response->supervisors as $supervisor) {
                $fish = $this->generateFish($supervisor->fish);
                    $user = User::where('pinfl', $supervisor->stir_or_pinfl)->first();
                    if ($user) {
                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        $user->roles()->attach($supervisor->role_id);
                    }


                    if (!$user) {
                        $user = User::create([
                            'name' => $fish ? $fish[1] : null,
                            'surname' => $fish ? $fish[0] : null,
                            'middle_name' => $fish ? $fish[2] : null,
                            'phone' => $supervisor->phone_number,
                            'login' => $supervisor->passport_number,
                            'password' => bcrypt($supervisor->stir_or_pinfl),
                            'user_status_id' => UserStatusEnum::ACTIVE,
                            'pinfl' => $supervisor->stir_or_pinfl,
                        ]);
                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        $user->roles()->attach($supervisor->role_id);
                    }
            }

            $article->users()->attach($response->inspector_id, ['role_id' => 3]);

            $this->acceptResponse($response);


            DB::commit();

            return $article;

        } catch (Exception $exception) {
            DB::rollBack();
            throw new NotFoundException($exception->getLine(), $exception->getCode(), );
        }

    }


    private function acceptResponse($response)
    {
        try {
            $authUsername = config('app.mygov.login');
            $authPassword = config('app.mygov.password');

            if ($response->object_type_id == 2) {
                $apiUrl = config('app.mygov.url').'/update/id/' . $response->task_id . '/action/issue-amount';
                $formName = 'IssueAmountV4FormNoticeBeginningConstructionWorks';
            } else {
                $apiUrl = config('app.mygov.linear').'/update/id/' . $response->task_id . '/action/issue-amount';
                $formName = 'IssueAmountFormRegistrationStartLinearObject';
            }

            $return =  Http::withBasicAuth($authUsername, $authPassword)
                ->post($apiUrl, [
                    $formName => [
                        "requisites" => $response->rekvizit->name,
                        "loacation_rep" => $response->region->name_uz.' '.$response->district->name_uz.' '.$response->location_building,
                        "name_rep" => $response->organization_name,
                        "amount" => $response->price_supervision_service
                    ]
                ]);

            if ($return->failed()) throw new NotFoundException($return->reason());

            return $return;
        }catch (\Exception $exception){
            throw new NotFoundException($exception->getMessage(), $exception->getCode(), );
        }

    }

    private function generateFish($name)
    {
        if ($name){
            $parts = explode(' ', $name);
            $array = [
                $parts[0] ?? null,
                $parts[1] ?? null,
                implode(' ', array_slice($parts, 2)) ?? null
            ];

        }
        return $array ?? null;
    }

}
