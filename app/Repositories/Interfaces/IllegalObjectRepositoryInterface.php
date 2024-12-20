<?php

namespace App\Repositories\Interfaces;

use App\Http\Requests\UpdateCheckListRequest;

interface IllegalObjectRepositoryInterface
{

    public function updateCheckList(UpdateCheckListRequest $request);
    public function createObject($data);
    public function updateObject(int $id);

    public function getQuestionList(int $id);
    public function getObject(int $id);

    public function getList(
        ?int    $regionId,
        ?int    $id,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $role_id
    );
}
