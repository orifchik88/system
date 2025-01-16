<?php

namespace App\Repositories\Interfaces;

interface ClaimRepositoryInterface
{


    // ---------------------- Begin ClaimResponse Methods ------------------------- //
    public function getResponseByGuId(int $guId);

    public function getActiveResponses();

    public function getExpiredTaskList();

    public function updateResponseStatus(int $guId, int $status);

    // ---------------------- End ClaimResponse Methods ------------------------- //
    public function getStatisticsRepeated(int $region = null): array;

    public function updateClaim(int $guId, array $data): bool;

    public function getStatisticsForInspector();

    public function getStatisticsCount(
        ?int    $regionId,
        ?int    $districtId,
        ?int    $expired,
        ?string $dateFrom,
        ?string $dateTo
    );

    public function organizationStatistics(
        int     $roleId,
        ?int $regionId,
        ?int $districtId,
        ?string $dateFrom,
        ?string $dateTo);

    public function getStatistics(?int $regionId, ?int $districtId);

    public function getClaimById(int $id, ?int $role_id);

    public function getClaimByGUID(int $guid);

    public function getObjects(int $id, ?array $filters, ?string $type);


    public function createClaim($consolidationGov, $expiryDate);

    public function getList(
        ?int    $regionId,
        ?int    $task_id,
        ?int    $object_task_id,
        ?string $name,
        ?string $customer,
        ?string $sender,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $expired,
        ?int    $role_id,
        ?string $start_date,
        ?string $end_date,
    );

    public function createOrganizationReview(int $claim_id, int $monitoring_id, int $organization_id, string $expiry_date);

    public function createMonitoring(array $blocks, array $organizations, int $id, int $object_id);

    public function updateConclusionOrganization(array $data, int $id, bool $status);
    public function manualConfirmByDirector(int $object_id, string $comment, string $file);
}
