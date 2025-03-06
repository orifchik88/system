<?php

namespace App\Services;

use App\Enums\ObjectStatusEnum;
use App\Enums\RoleTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Resources\ArticlePalataResource;
use App\Http\Resources\DifficultyCategoryResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\ObjectDesignResource;
use App\Http\Resources\ObjectOrganizationResource;
use App\Http\Resources\ObjectTypeResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RegulationStatusResource;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

class MyGovService
{
    private ArticleRepositoryInterface $articleRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        UserRepositoryInterface    $userRepository,
        protected QuestionService   $questionService,
    )
    {
        $this->articleRepository = $articleRepository;
        $this->userRepository = $userRepository;
    }

    public function getDxaTaskById(int $task_id)
    {
        $object = $this->articleRepository->findByTaskId($task_id);

        if (!$object || $object->object_status_id == ObjectStatusEnum::SUBMITTED)
            return null;

        return [
            'success' => true,
            'object_type' => $object->objectType->name
        ];
    }

    public function getObjectTaskById(int $task_id)
    {
        $object = $this->articleRepository->findByTaskId($task_id);
        if (!$object)
            return null;

        $customer_inn = "";
        $customer_name = "";
        $vendor_inn = "";
        $vendor_name = "";

        foreach ($object->users as $user) {
            if ($user->pivot->role_id == 8) {
                $customer_inn = $user->identification_number;
                $customer_name = $user->organization_name;
            }
            if ($user->pivot->role_id == 10) {
                $vendor_inn = $user->identification_number;
                $vendor_name = $user->organization_name;
            }
        }

        return [
            'request_number' => $task_id,
            'object_name' => $object->name,
            'created_at' => $object->created_at,
            'deadline' => $object->deadline,
            'inn' => $customer_inn,
            'fullname' => $customer_name,
            'vendor_name' => $vendor_name,
            'vendor_inn' => $vendor_inn,
            'status' => $object->object_status_id,
            'gnk_id' => $object->gnk_id,
            'file_urls' => []
        ];
    }

    public function getObjectsByPinfl($pinfl)
    {
        $objects = $this->userRepository->findByPinfl($pinfl);

        if (!$objects)
            return null;

        $responseArr = [];
        $tmpArr[UserRoleEnum::ICHKI->value] = [];
        $tmpArr[UserRoleEnum::TEXNIK->value] = [];
        $tmpArr[UserRoleEnum::MUALLIF->value] = [];

        foreach ($objects->objects()->where('object_status_id', ObjectStatusEnum::PROGRESS)->get() as $object) {
            $tmpArr[$object->getOriginal('pivot_role_id')][] = $object->id;
        }

        $responseArr['description'] = "Bitta xodimga bir vaqtning o‘zida ko‘pi bilan 10 ta  obyekt biriktirilishi mumkin.";
        $responseArr['ichki_nazorat'] = [
            'objects' => count($tmpArr[UserRoleEnum::ICHKI->value]),
            'can_assign' => !((count($tmpArr[UserRoleEnum::ICHKI->value]) >= 10))
        ];
        $responseArr['texnik_nazorat'] = [
            'objects' => count($tmpArr[UserRoleEnum::TEXNIK->value]),
            'can_assign' => !((count($tmpArr[UserRoleEnum::TEXNIK->value]) >= 10))
        ];
        $responseArr['muallif_nazorat'] = [
            'objects' => count($tmpArr[UserRoleEnum::MUALLIF->value]),
            'can_assign' => !((count($tmpArr[UserRoleEnum::MUALLIF->value]) >= 10))
        ];

        return $responseArr;
    }

    public function getObjectsByCustomer($pinfl)
    {
        $objects = $this->userRepository->findByPinfl($pinfl);
        if (!$objects)
            return null;

        $objectsArr = [];
        foreach ($objects->objects()->get() as $object) {
            if ($object->object_status_id == ObjectStatusEnum::PROGRESS) {
                $tmpArr['id'] = $object->id;
                $tmpArr['name'] = $object->name;
                $tmpArr['task_id'] = $object->task_id;
                $objectsArr[] = $tmpArr;
            }
        }
        return $objectsArr;
    }

    public function getObjectsByCadastralNumber($number)
    {
        $objects = $this->articleRepository->findByCadastralNumber($number);
        if (!$objects)
            return null;

        $objectsArr = [];
        foreach ($objects as $object) {
            if ($object->object_status_id == ObjectStatusEnum::SUBMITTED) {
                $tmpArr['name'] = $object->name;
                $tmpArr['task_id'] = $object->task_id;
                $tmpArr['region'] = RegionResource::make($object->region);
                $tmpArr['district'] = DistrictResource::make($object->district);
                $tmpArr['address'] = $object->address;
                $tmpArr['buyurtmachi']['organization_name'] = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first()?->organization_name ?? null;
                $tmpArr['buyurtmachi']['inn'] = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first()?->identification_number ?? null;
                $tmpArr['ichki_nazorat']['fish'] = $object->users()->where('role_id', UserRoleEnum::ICHKI->value)->first()?->full_name ?? null;
                $tmpArr['ichki_nazorat']['phone'] = $object->users()->where('role_id', UserRoleEnum::ICHKI->value)->first()?->phone ?? null;
                $objectsArr[] = $tmpArr;
            }
        }
        return $objectsArr;
    }

    public function getObjectList($filters)
    {
        $objects = $this->articleRepository->getList($filters);
        if (!$objects)
            return null;

        $meta = [
            "success" => true,
            "status" => 200,
            "msg" => "Сўровга асосан маълумотлар тўлиқ шакллантирилди",
            "title" => "Обектлар",
            "total_counts" => $objects->total(),
            "description" => [
                'doc_id' => 'Маълумотнинг уникал рақами',
                'app_date' => 'Ариза берилган  сана',
                'app_number' => 'Ариза рақами',
                'obj_name' => 'Объект номи',
                'region_id' => 'Объект ҳудуди коди (СОАТО)',
                'region_name' => 'Объект ҳудуди номи',
                'district_id' => 'Объект тумани (шаҳар) коди (СОАТО)',
                'district_name' => 'Объект тумани (шаҳар) номи',
                'customer_name' => 'Буюртмачи номи',
                'customer_tin' => 'Буюртмачининг СТИРи',
                'builder_name' => 'Пудратчи ташкилот номи',
                'builder_tin' => 'Пудратчи ташкилот СТИРи',
                'rating' => 'Рейтинг кўрсаткичлари',
                'deadline' => 'Қурилиш муддати',
                'finish_date' => 'Объектни фойдаланишга топширилган сана',
                'obj_type' => 'Объект тури',
                'complexity' => 'Мураккаблик тоифаси',
                'build_type' => 'Қурилиш тури',
                'build_cost' => 'Қурилиш қиймати',
                'industry' => 'Қурилиш соҳаси номи',
                'funding' => 'Молиялаштириш манбаи',
            ],
            'data' => ArticlePalataResource::collection($objects->makeHidden(['appends'])),
        ];

        return response()->json($meta, 200);

    }

    public function getObjectOrganization()
    {
        $objects = $this->articleRepository->getList(null);

        if (!$objects)
            return null;

        $response = [
            'objects' => ObjectOrganizationResource::collection($objects),
            'meta' => pagination($objects)
        ];

        return response()->json($response, 200);

    }

    public function getObjectDesign()
    {
        $objects = $this->articleRepository->getList(null);
        if (!$objects)
            return null;

        $response = [
            'objects' => ObjectDesignResource::collection($objects),
            'meta' => pagination($objects)
        ];

        return response()->json($response, 200);
    }

    public function getObjectsRegulations($filters)
    {
        $objects = $this->articleRepository->findByReestr($filters)->get();
        if (!$objects)
            return null;

        $response = $objects->load(['regulations.createdByUser', 'regulations.createdByRole', 'regulations.responsibleUser', 'regulations.responsibleRole'])->map(function ($object) {
            return [
                'registration_number' => $object->task_id,
                'registration_date' => $object->created_at,
                'lat' =>$object->lat,
                'long' => $object->long,
                'closed_at' => $object->closed_at,
                'regulations' => $object->regulations->map(function ($regulation) {
                    return [
                        'status' => RegulationStatusResource::make($regulation->regulationStatus),
                        'regulation_number' => $regulation->regulation_number,
                        'from_user' => [
                            'id' => $regulation->createdByUser->id ?? null,
                            'name' => $regulation->createdByUser->name ?? null,
                            'middle_name' => $regulation->createdByUser->middle_name ?? null,
                            'surname' => $regulation->createdByUser->surname ?? null,
                        ],
                        'from_role' => [
                            'id' => $regulation->createdByRole->id ?? null,
                            'name' => $regulation->createdByRole->name ?? null,
                        ],
                        'to_user' => [
                            'id' => $regulation->responsibleUser->id,
                            'name' => $regulation->responsibleUser->name,
                            'middle_name' => $regulation->responsibleUser->middle_name,
                            'surname' => $regulation->responsibleUser->surname,
                        ],
                        'to_role' => [
                            'id' => $regulation->responsibleRole->id,
                            'name'=> $regulation->responsibleRole->name,
                        ],
                        'pdf' => $regulation->pdf,
                    ];
                }),
            ];
        });

        return response()->json($response, 200);
    }

    public function getObjectTax($objectId)
    {
        $object = $this->articleRepository->findById($objectId);
        if (!$object) return null;

        if ($object->funding_source_id != 1 && $object->object_type_id != 1) {
            $customer = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
            $builder = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

            $blocks = $object->blocks()->with('type')->get();

            $blockWorkTypes = $blocks->map(function ($block) {
                $works = $this->questionService->getQuestionList(
                    blockId: $block->id,
                    type: null,
                    block_type: 2
                );

                $workTypes = collect($works)
                    ->reject(fn($item) => in_array($item['work_type_id'], [14]))
                    ->flatMap(function ($item) {
                        $title = $item['name'];
                        $questions = $item['questions'];

                        $filteredQuestions = collect($questions)->groupBy('floor');

                        return $filteredQuestions->map(function ($questions, $floor) use ($title) {
                            $name = $floor && $floor != '' ? $floor . ' - ' . $title : $title;

                            $firstQuestion = $questions->first();
                            if (!$firstQuestion || $firstQuestion['work_type_status']->value != 2) {
                                return null;
                            }

                            return [
                                'name' => $name,
                            ];
                        })->filter();
                    })
                    ->values()
                    ->toArray();




                return [
                    'id' => $block->id,
                    'name' => $block->name,
                    'floor' => $block->floor,
                    'work_types' => $workTypes,
                ];
            });

            $data = [
                'object_id' => $object->id,
                'object_name' => $object->name,
                'created_at' => $object->created_at,
                'closed_at' => $object->closed_at,
                'customer_name' => $customer?->organization_name ?? '',
                'builder_name' => $builder?->organization_name ?? '',
                'cadastral_number' => $object->cadastral_number,
                'difficulty_category' => $object->difficulty ? DifficultyCategoryResource::make($object->difficulty) : null,
                'construction_works' => $object->construction_works,
                'region' => $object->region ? $object->region->only(['name_uz', 'soato']) : null,
                'district' => $object->district ? $object->district->only(['name_uz', 'soato']) : null,
                'object_type' => $object->objectType ? $object->objectType->only(['id','name']) : null,
                'address' => $object->location_building,
                'number_protocol' => $object->number_protocol,
                'reestr_number' => $object->reestr_number,
                'funding_source' => $object->fundingSource ? $object->fundingSource->only(['id', 'description']) : null,
                'construction_cost' => $object->construction_cost,
                'pinfl_customer' => $customer?->name ? $customer->pinfl : '',
                'tin_customer' => $customer?->name ? '' : ($customer?->pinfl ?? ''),
                'tin_general_contractor' => $builder?->pinfl ?? '',
                'blocks' => $blockWorkTypes,
            ];

            return response()->json($data, 200);
        }

        return null;
    }

    public function getObjectByReestr($filters)
    {
        $object = $this->articleRepository->findByReestr($filters)->lastest();
        if (!$object)
            return null;

        $data = [

        ];
    }
}
