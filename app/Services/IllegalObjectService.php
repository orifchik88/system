<?php

namespace App\Services;

use App\Http\Requests\CreateIllegalObjectRequest;
use App\Http\Requests\UpdateCheckListRequest;
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

    public function createObject(CreateIllegalObjectRequest $request)
    {
        return $this->illegalObjectRepository->createObject(data: $request);
    }

    public function getQuestionList(int $id)
    {
        return $this->illegalObjectRepository->getQuestionList(id: $id);
    }

    public function getObjectList(
        ?int    $regionId,
        ?int    $id,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $role_id
    )
    {
        return $this->illegalObjectRepository->getList(
            regionId: $regionId,
            id: $id,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            role_id: $role_id
        );
    }

}
