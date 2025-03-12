<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Http\Requests\CreateIllegalObjectRequest;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\Article;
use App\Repositories\Interfaces\IllegalObjectRepositoryInterface;

class IllegalObjectService
{
    private IllegalObjectRepositoryInterface $illegalObjectRepository;

    public function __construct(IllegalObjectRepositoryInterface $illegalObjectRepository)
    {
        $this->illegalObjectRepository = $illegalObjectRepository;
    }

    public function updateCheckLIst(UpdateCheckListRequest $request)
    {
        return $this->illegalObjectRepository->updateCheckList(request: $request);
    }

    public function createObject(CreateIllegalObjectRequest $request, $user, $roleId)
    {
        return $this->illegalObjectRepository->createObject(data: $request, user: $user, roleId: $roleId);
    }

    public function saveObject($id)
    {
        return $this->illegalObjectRepository->updateObject(id: $id);
    }

    public function getStatistics(
        ?int    $regionId,
        ?string $dateFrom,
        ?string $dateTo,
    )
    {
        return $this->illegalObjectRepository->getStatistics(
            regionId: $regionId,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );
    }

    public function getQuestionList(int $id)
    {
        return $this->illegalObjectRepository->getQuestionList(id: $id);
    }

    public function getObject(int $id)
    {
        return $this->illegalObjectRepository->getObject(id: $id);
    }

    public function getObjectList($user,$roleId, $filters)
    {
        switch ($roleId) {

            case UserRoleEnum::KVARTIRA_INSPECTOR->value:
            case UserRoleEnum::GASN_INSPECTOR->value:
            case UserRoleEnum::SUV_INSPECTOR->value:
                return $this->getByUserId($user, $filters);
            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
                return $this->getAll($filters);
            default:
                return null;
        }
    }

    private function getByUserId($user, $filters)
    {
        return $this->illegalObjectRepository->getList(
            user: $user, roleId: null, filters: $filters
        );
    }

    private function getByRoleId($roleId, $filters)
    {
        return $this->illegalObjectRepository->getList(
            user: null, roleId: $roleId, filters: $filters
        );
    }

    public function getAll($filters)
    {
            return $this->illegalObjectRepository->getList(
                user: null, roleId: null, filters: $filters
            );
    }



}
