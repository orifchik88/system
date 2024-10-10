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
        ?int $regionId,
        ?int $expired,
        ?string $dateFrom,
        ?string $dateTo
    );
    public function getStatistics(?int $regionId, ?int $districtId);
    public function getClaimById(int $id);
    public function getClaimByGUID(int $guid);

    public function createClaim($consolidationGov, $expiryDate);

    public function getList(
        ?int    $regionId,
        ?string $main,
        ?string $dateFrom,
        ?string $dateTo,
        ?int    $status,
        ?int    $expired,
    );
}
