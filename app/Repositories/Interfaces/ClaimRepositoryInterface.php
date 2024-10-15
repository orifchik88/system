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

    public function getStatisticsCount(
        ?int    $regionId,
        ?int    $expired,
        ?string $dateFrom,
        ?string $dateTo
    );

    public function organizationStatistics(
        int     $roleId,
        ?string $dateFrom,
        ?string $dateTo);

    public function getStatistics(?int $regionId, ?int $districtId);

    public function getClaimById(int $id, ?int $role_id);

    public function getClaimByGUID(int $guid);

    public function getObjects(int $id);

    public function createClaim($consolidationGov, $expiryDate);

    public function getList(
        ?int    $regionId,
        ?int    $task_id,
        ?string $name,
        ?string $customer,
        ?string $sender,
        ?int    $districtId,
        ?string $sortBy,
        ?int    $status,
        ?int    $expired,
        ?int    $role_id
    );

    public function createOrganizationReview(int $claim_id, int $monitoring_id, int $organization_id, string $expiry_date);

    public function createMonitoring(array $blocks, array $organizations, int $id, int $object_id);

    public function updateConclusionOrganization(array $data, int $id, bool $status);
}
