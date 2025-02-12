<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Models\Monitoring;
use App\Repositories\Interfaces\MonitoringRepositoryInterface;

class MonitoringService
{

    public function __construct(
        protected MonitoringRepositoryInterface $monitoringRepository
    )
    {
    }

    public function getMonitorings($user, $roleId)
    {
        switch ($roleId) {
            case UserRoleEnum::INSPECTOR->value:
            case UserRoleEnum::ICHKI->value:
            case UserRoleEnum::MUALLIF->value:
            case UserRoleEnum::TEXNIK->value:
            case UserRoleEnum::LOYIHA->value:
            case UserRoleEnum::BUYURTMACHI->value:
            case UserRoleEnum::QURILISH->value:
                return $this->getMonitoringByUserRole($user, $roleId);
            case UserRoleEnum::INSPEKSIYA->value:
            case UserRoleEnum::HUDUDIY_KUZATUVCHI->value:
            case UserRoleEnum::QURILISH_MONTAJ->value:
            case UserRoleEnum::OPERATOR->value:
                return $this->getMonitoringByRegion($user->region_id);
            case UserRoleEnum::RESPUBLIKA_KUZATUVCHI->value:
                return Monitoring::query()->with(['regulations', 'checklists', 'regulationType', 'role', 'user']);
            default:
                return Monitoring::query()->whereRaw('1 = 0');

        }
    }

    public function getMonitoringList(array $filters)
    {
        if(!isset($filters['year']))
            $filters['year'] = date('Y');
        if(!isset($filters['month']))
            $filters['month'] = date('m');

        return $this->monitoringRepository->getMonitoringList(filters: $filters);
    }

    public function getMonitor()
    {

    }



    public function getMonitoringByRegion($regionId)
    {
        return $this->monitoringRepository->getMonitoringByRegion($regionId);
    }

    public function getMonitoringByUserRole($user, $roleId)
    {
        return $this->monitoringRepository->getMonitoringByUserRole($user, $roleId);
    }

    public function searchMonitoring($query, $filters)
    {
        return $this->monitoringRepository->searchMonitorings($query, $filters);
    }


}
