<?php

namespace App\Services;

use App\Enums\ObjectStatusEnum;
use App\Enums\RoleTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Resources\ArticlePalataResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
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

        $response = [
            'objects' => ArticlePalataResource::collection($objects),
            'meta' => pagination($objects)
        ];

        return response()->json($response, 200);

    }

    public function getObjectOrganization()
    {
        $objects = $this->articleRepository->getList(null);

        if (!$objects)
            return null;

        $objectsArr = [];
        foreach ($objects as $object) {

            $customer  = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();

            $tmpArr['send_id'] = $object->id;
            $tmpArr['send_date'] = $object->created_at;
            $tmpArr['applicant_physical_name'] = $customer ? $customer->name : null;
            $tmpArr['passport_applicant_physical'] = null;
            $tmpArr['pinfl_applicant_physical'] = $customer ? ($customer->name ? $customer->pinfl : null) : null;
            $tmpArr['tin_applicant_physical'] =  null;
            $tmpArr['address_physical'] = '';
            $tmpArr['phone_applicant_physical'] =  $customer ? ($customer->name ? $customer->phone  : null) : null;
            $tmpArr['e_mail_physical'] = null;
            $tmpArr['applicant_name'] = $customer?->organization_name ?? null;
            $tmpArr['tin_applicant'] = $customer ? ($customer->name ? null : $customer->tin) : null;
            $tmpArr['bank_number'] = null;
            $tmpArr['economic_activity'] = null;
            $tmpArr['address'] = null;
            $tmpArr['phone_number'] = null;
            $tmpArr['e_mail'] = null;
            $tmpArr['object_name'] = $object->name;
            $tmpArr['object_location'] = $object->region?->name_uz.' '. $object->distict?->name_uz. ' ' . $object->location_building;
            $tmpArr['object_category'] = $object->difficulty?->difficulty;
            $tmpArr['type_construction'] = $object->construction_works ?? null;
            $tmpArr['construction_conclusion'] = $object->number_protocol ?? null;
            $tmpArr['expertise_conclusion'] = $object->reestr_number ?? null;
            $objectsArr[] = $tmpArr;

        }
        return $objectsArr;

    }

    public function getObjectDesign()
    {
        $objects = $this->articleRepository->getList(null);
        if (!$objects)
            return null;

        $objectsArr = [];
        foreach ($objects as $object) {
            $customer  = $object->users()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first();
            $designer = $object->users()->where('role_id', UserRoleEnum::LOYIHA->value)->first();
            $builder = $object->users()->where('role_id', UserRoleEnum::QURILISH->value)->first();

            $tmpArr['send_id'] = $object->id;
            $tmpArr['send_date'] = $object->created_at;
            $tmpArr['customer_name']  = $customer ? ($customer->name ? $customer->full_name : $customer->organization_name) : null;
            $tmpArr['customer_address']  = null;
            $tmpArr['object_name'] = $object->name;
            $tmpArr['object_address'] = $object->region?->name_uz.' '. $object->distict?->name_uz. ' ' . $object->location_building;
            $tmpArr['start_date'] =  Carbon::parse($object->created_at)->format('Y-m-d');
            $tmpArr['finish_date'] = $object->deadline;
            $tmpArr['designer_name'] = $designer ? ($designer->name ? $designer->full_name : $designer->organization_name) : null;
            $tmpArr['designer_address'] = null;
            $tmpArr['constructor_name'] = $builder ? ($builder->name ? $builder->full_name : $builder->organization_name) : null;
            $tmpArr['constructor_INN'] = $builder->pinfl ?? null;
            $tmpArr['constructor_address'] =  null;

            $objectsArr[] = $tmpArr;
        }
        return $objectsArr;
    }
}
