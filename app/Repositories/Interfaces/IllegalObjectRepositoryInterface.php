<?php

namespace App\Repositories\Interfaces;

use App\Http\Requests\IllegalObjectUpdateRequest;
use App\Http\Requests\UpdateCheckListRequest;

interface IllegalObjectRepositoryInterface
{

    public function updateCheckList(UpdateCheckListRequest $request, $user, $roleId);
    public function createObject($data, $user, $roleId);

    public function insertObject(IllegalObjectUpdateRequest $request, $user, $roleId);
    public function updateObject(int $id);
    public function getStatistics(
        ?int    $regionId,
        ?string $dateFrom,
        ?string $dateTo
    );
    public function getQuestionList(int $id);
    public function getObject(int $id);

    public function getList(
        ?object $user,
        ?int $roleId,
        ?array $filters
    );

    public function getObjectHistory($id);
    public function getChecklistHistory($id);
}
