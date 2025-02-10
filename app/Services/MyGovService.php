<?php

namespace App\Services;

use App\Enums\ObjectStatusEnum;
use App\Enums\RoleTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Resources\ArticlePalataResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\ObjectDesignResource;
use App\Http\Resources\ObjectOrganizationResource;
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
        UserRepositoryInterface    $userRepository
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
        $object = $this->articleRepository->findByReestr($filters);
        if (!$object)
            return null;


        $response = [
            'registration_number' => $object->task_id,
            'registration_date' => $object->created_at,
            'closed_at' => $object->closed_at,
            'regulations' => $object->regulations->map(function ($regulation) {
                $fromUser = User::query()->find($regulation->created_by_user_id);
                $fromRole = Role::query()->find($regulation->created_by_role_id);
                return [
                    'status' => RegulationStatusResource::make($regulation->regulationStatus),
                    'from_user' => [
                        'id' => $fromUser->id,
                        'name' => $fromUser->name,
                        'middle_name' => $fromUser->middle_name,
                        'surname' => $fromUser->surname,
                    ],
                    'role' => [
                        'id' => $fromRole->id,
                        'name'=> $fromRole->name,
                    ],
                    'pdf' => $regulation->pdf,
                ];
            }),
        ];

        return response()->json($response, 200);
    }
}
