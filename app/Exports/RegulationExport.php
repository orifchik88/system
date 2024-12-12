<?php

namespace App\Exports;

use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use App\Services\RegulationService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegulationExport implements FromCollection, WithHeadings
{

    public function __construct(
        protected ?int $regionId
    )
    {

    }

    public function collection()
    {

         return  Regulation::query()
            ->whereHas('object', function ($query) {
                $query->where('region_id', $this->regionId);
            })
            ->where(function ($query) {
                $query->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)
                    ->orWhereNotNull('lawyer_status_id');
            })
            ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
            ->get()
            ->map(function ($regulation) {
                return [
                    $regulation->object->task_id ?? '',
                    $regulation->object->name ?? '',
                    $regulation->regulation_number ?? '',
                    $regulation->regulationUser ?  Role::query()->find($regulation->regulationUser->from_role_id)->name : '',
                    $regulation->regulationUser ? Role::query()->find($regulation->regulationUser->to_role_id)->name : '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Ariza raqami',
            'Buyurtmachi nomi',
            'Buyurtmachi INN',
            'Obyekt nomi',
            'Viloyat',
            'Tuman',
        ];
    }
}
