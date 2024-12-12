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

        $roles = Role::all()->keyBy('id');

        return Regulation::query()
            ->with([
                'object',
                'regulationUser',
                'violations',
            ])
            ->whereHas('object', function ($query) {
                $query->where('region_id', $this->regionId);
            })
            ->where(function ($query) {
                $query->where('regulation_status_id', RegulationStatusEnum::IN_LAWYER)
                    ->orWhereNotNull('lawyer_status_id');
            })
            ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
            ->get()
            ->map(function ($regulation) use ($roles) {
                return [
                    $regulation->object->task_id ?? '',
                    $regulation->object->name ?? '',
                    $regulation->regulation_number ?? '',
                    $roles[$regulation->regulationUser->from_role_id]->name ?? '',
                    $roles[$regulation->regulationUser->to_role_id]->name ?? '',
                    $regulation->violations->count(),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Obyekt raqami',
            'Obyekt nomi',
            'Qoidabuzarlik raqami',
            'Kimdan',
            'Kimga',
            'Qoidabuzarlik soni',
        ];
    }
}
