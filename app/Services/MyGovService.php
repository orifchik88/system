<?php

namespace App\Services;

use App\Enums\ObjectStatusEnum;
use App\Enums\RoleTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

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
        if (!$object)
            return null;

        return [
            'success' => true,
            'object_type' => $object->objectType->name
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
        $objectsArr = [];
        foreach ($objects as $object) {
            $tmpArr['id'] = $object->id;
            $tmpArr['name'] = $object->name;
            $tmpArr['region'] = $object?->region->name_uz;
            $tmpArr['region_id'] = $object->region_id;
            $tmpArr['district'] = $object?->district->name_uz;
            $tmpArr['district_id'] = $object->district_id;
            $tmpArr['address'] = $object->address;
            $tmpArr['difficulty_category_name'] = $object->difficulty->difficulty;
            $tmpArr['difficulty_category_id'] = $object->difficulty_category_id;
            $tmpArr['construction_type'] = $object?->response->construction_works;
            $tmpArr['construction_cost'] = $object->cost;
            $tmpArr['blocks'] = $object?->blocks()->pluck('id');
            $tmpArr['object_status_id'] = $object->object_status_id;
            $tmpArr['object_status'] = $object?->objectStatus->name;
            $tmpArr['created_at'] = $object->created_at;
            $tmpArr['updated_at'] = $object->updated_at;
            $tmpArr['deleted_at'] = $object->deleted_at;
            $tmpArr['object_type'] = $object->object_type_id;
            $tmpArr['cadastral_number'] = $object->cadastral_number;
            $tmpArr['name_expertise'] = $object->name_expertise;
            $tmpArr['lat'] = $object->lat;
            $tmpArr['long'] = $object->long;
            $tmpArr['dxa_id'] = $object->dxa_response_id;
            $tmpArr['task_id'] = $object->task_id;
            $tmpArr['funding_source'] = $object?->fundingSource->name;
            $tmpArr['funding_source_id'] = $object->funding_source_id;
            $tmpArr['closed_at'] = $object->closed_at;
            $tmpArr['object_sector'] = $object->objectSector->name;
            $tmpArr['object_sector_id'] = $object->object_sector_id;
            $tmpArr['deadline'] = $object->deadline;
            $tmpArr['gnk_id'] = $object->gnk_id;


            $objectsArr[] = $tmpArr;
        }
        return $objectsArr;
    }
}
