<?php

namespace App\Services;

use App\DTO\ObjectDto;
use App\Enums\BlockModeEnum;
use App\Enums\DifficultyCategoryEnum;
use App\Enums\DxaResponseStatusEnum;
use App\Enums\LevelStatusEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\ObjectTypeEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\CheckList;
use App\Models\DxaResponse;
use App\Models\FundingSource;
use App\Models\Level;
use App\Models\ObjectSector;
use App\Models\ObjectType;
use App\Models\Question;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use App\Models\WorkType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPUnit\Exception;


class ArticleService
{
    protected ObjectDto $objectDto;

    public function __construct(protected Article       $article,
                                protected ObjectType    $objectType,
                                protected FundingSource $fundingSource,
                                protected ObjectSector  $objectSector,
                                protected Question      $question,
                                protected DxaResponse   $dxaResponse,)
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

            if ($response->notification_type==2)
            {
                $article = Article::query()->where('task_id', $response->old_task_id)->first();
                $article->update([
                    'name' => $response->object_name,
                    'region_id' => $response->region_id,
                    'district_id' => $response->district_id,
                    'object_type_id' => $response->object_type_id,
                    'organization_name' => $response->organization_name,
                    'location_building' => $response->location_building,
                    'address' => $response->address,
                    'construction_works' =>$response->construction_works,
                    'cadastral_number' => $response->cadastral_number,
                    'name_expertise' => $response->name_expertise,
                    'difficulty_category_id' => DifficultyCategoryEnum::fromString($response->category_object_dictionary),
                    'construction_cost' => $response->cost,
                    'organization_projects' => $response->organization_projects,
                    'specialists_certificates' => $response->specialists_certificates,
                    'contract_file' => $response->contract_file,
                    'confirming_laboratory' => $response->confirming_laboratory,
                    'file_energy_efficiency' => $response->file_energy_efficiency,
                    'legal_opf' => $response->legal_opf,
                    'sphere_id' => $response->sphere_id,
                    'program_id' => $response->program_id,
                    'linear_type' => $response->linear_type,
                    'dxa_response_id' => $response->id,
                    'price_supervision_service' => price_supervision($response->cost),
                    'task_id' => $response->task_id,
                    'number_protocol' => $response->number_protocol,
                    'positive_opinion_number' => $response->positive_opinion_number,
                    'date_protocol' =>$response->date_protocol,
                    'funding_source_id' => $response->funding_source_id,
                    'deadline' => $response->end_term_work,
                    'gnk_id' => $response->gnk_id,
                    'reestr_number' => (int)$response->reestr_number,
                ]);
            }else{
                $article = new Article();
                $article->name = $response->object_name;
                $article->region_id = $response->region_id;
                $article->district_id = $response->district_id;
                $article->object_status_id = ObjectStatusEnum::PROGRESS;
                $article->object_type_id = $response->object_type_id;
                $article->organization_name = $response->organization_name;
                $article->location_building = $response->location_building;
                $article->address = $response->address;
                $article->cadastral_number = $response->cadastral_number;
                $article->name_expertise = $response->name_expertise;
                $article->difficulty_category_id = DifficultyCategoryEnum::fromString($response->category_object_dictionary);
                $article->construction_cost = $response->cost;
                $article->sphere_id  = $response->sphere_id;
                $article->program_id  = $response->program_id;
                $article->construction_works  = $response->construction_works;
                $article->linear_type  = $response->linear_type;
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
                $article->gnk_id = $response->gnk_id;
                $article->reestr_number = (int)$response->reestr_number;
                $article->save();
            }



            if ($response->notification_type==2)
            {

                $article->users()->detach();
            }


            foreach ($response->supervisors as $supervisor) {
                $fish = $this->generateFish($supervisor->fish);
                    $user = User::where('pinfl', $supervisor->stir_or_pinfl)->first();
                    if ($user) {
                            $user->update([
                                'name' => $fish ? $fish[1] : null,
                                'surname' => $fish ? $fish[0] : null,
                                'middle_name' => $fish ? $fish[2] : null,
                                'phone' => $supervisor->phone_number,
                                'login' => $supervisor->passport_number,
                                'organization_name' => $supervisor->organization_name,
                                'password' => bcrypt($supervisor->stir_or_pinfl),
                                'user_status_id' => UserStatusEnum::ACTIVE,
                                'pinfl' => $supervisor->stir_or_pinfl,
                                'identification_number' => $supervisor->identification_number,
                            ]);


                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        if (!$user->roles()->where('role_id', $supervisor->role_id)->exists())
                            UserRole::query()->create([
                                'user_id' => $user->id,
                                'role_id' => $supervisor->role_id,
                            ]);
                    }
                    if (!$user) {
                        $user = User::create([
                            'name' => $fish ? $fish[1] : null,
                            'surname' => $fish ? $fish[0] : null,
                            'middle_name' => $fish ? $fish[2] : null,
                            'phone' => $supervisor->phone_number,
                            'login' => $supervisor->passport_number,
                            'organization_name' => $supervisor->organization_name,
                            'password' => bcrypt($supervisor->stir_or_pinfl),
                            'user_status_id' => UserStatusEnum::ACTIVE,
                            'pinfl' => $supervisor->stir_or_pinfl,
                            'identification_number' => $supervisor->identification_number,
                        ]);
                        $article->users()->attach($user->id, ['role_id' => $supervisor->role_id]);
                        UserRole::query()->create([
                            'user_id' => $user->id,
                            'role_id' => $supervisor->role_id,
                        ]);
                    }
            }


            $article->users()->attach($response->inspector_id, ['role_id' => 3]);

            $this->acceptResponse($response);
            $this->saveBlocks($response, $article);
            $this->saveEmployee($article);


            DB::commit();

            return $article;

        } catch (Exception $exception) {
            DB::rollBack();
            throw new NotFoundException($exception->getLine(), $exception->getCode(), );
        }

    }

    private function saveEmployee($object)
    {
        $rating = [];
        $muallif = $object->users()->where('role_id', UserRoleEnum::MUALLIF->value)->first();
        $texnik = $object->users()->where('role_id', UserRoleEnum::TEXNIK->value)->first();
        $buyurtmachi = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
        $loyiha = $object->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
        $ichki = $object->users()->where('role_id', UserRoleEnum::ICHKI->value)->first();
        $qurilish = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

        $loyihaRating = getData(config('app.gasn.rating'), (int)$loyiha->identification_number);
        if (!$loyihaRating) throw new NotFoundException('Loyiha tashkilotining reytinggini olishda muammo');

        $qurilishRating = getData(config('app.gasn.rating'), (int)$qurilish->identification_number);

        if (!$qurilishRating) throw new NotFoundException('Qurilish tashkilotining reytinggini olishda muammo');

        $rating[] = [
            'loyiha' => $loyihaRating['data']['data'],
            'qurilish' => $qurilishRating['data']['data'],
        ];

        $object->update([
            'rating' => json_encode($rating)
        ]);

        if (!UserEmployee::query()->where('user_id', $texnik->id)->where('parent_id', $buyurtmachi->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $texnik->id,
                'parent_id' => $buyurtmachi->id,
            ]);
        }

        if (!UserEmployee::query()->where('user_id', $muallif->id)->where('parent_id', $loyiha->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $muallif->id,
                'parent_id' => $loyiha->id,
            ]);
        }

        if (!UserEmployee::query()->where('user_id', $ichki->id)->where('parent_id', $qurilish->id)->exists()) {
            UserEmployee::query()->create([
                'user_id' => $ichki->id,
                'parent_id' => $qurilish->id,
            ]);
        }
    }


    private function createChecklist($question, $levelId, $workTypeId, $blockId, $objectTypeId, $articleId)
    {
        $checklist = new Checklist();
        $checklist->name = $question->name;
        $checklist->question_id = $question->id;
        $checklist->level_id = $levelId;
        $checklist->work_type_id = $workTypeId;
        $checklist->block_id = $blockId;
        $checklist->object_type_id = $objectTypeId;
        $checklist->article_id = $articleId;
        $checklist->save();
    }



    private function saveBlocks($response, $article)
    {
        $blocks = $response->blocks;

        foreach ($blocks as $block) {
            $block->update([
               'article_id' => $article->id,
            ]);
        }
    }




    private function acceptResponse($response)
    {
        try {
            if (env('APP_ENV') === 'development') {
                $authUsername = config('app.mygov.login');
                $authPassword = config('app.mygov.password');

                if ($response->object_type_id == 2) {
                    $apiUrl = config('app.mygov.url') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountV4FormNoticeBeginningConstructionWorks';
                } else {
                    $apiUrl = config('app.mygov.linear') . '/update/id/' . $response->task_id . '/action/issue-amount';
                    $formName = 'IssueAmountFormRegistrationStartLinearObject';
                }

                $return = Http::withBasicAuth($authUsername, $authPassword)
                    ->post($apiUrl, [
                        $formName => [
                            "requisites" => $response->rekvizit->name,
                            "loacation_rep" => $response->region->name_uz . ' ' . $response->district->name_uz . ' ' . $response->location_building,
                            "name_rep" => $response->organization_name,
                            "amount" => $response->price_supervision_service
                        ]
                    ]);

                if ($return->failed()) throw new NotFoundException($return->reason());
            }

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
