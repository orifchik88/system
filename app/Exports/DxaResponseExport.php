<?php

namespace App\Exports;

use App\Enums\UserRoleEnum;
use App\Models\DxaResponse;
use App\Models\User;
use App\Services\DxaResponseService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DxaResponseExport implements FromCollection, WithHeadings
{

    public function __construct(
        protected DxaResponseService $service,
        protected ?int $type,
        protected  $user,
        protected ?int$roleId
    )
    {

    }

    public function collection()
    {
        return  $this->service->getRegisters($this->user, $this->roleId, $this->type)
            ->get()
            ->map(function ($dxaResponse) {
                return [
                    $dxaResponse->task_id,
                    $dxaResponse->organization_name,
                    $dxaResponse->supervisors()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first()->identification_number ?? '',
                    $dxaResponse->object_name,
                    $dxaResponse->region->name_uz ?? '',
                    $dxaResponse->district->name_uz ?? '',
                    $dxaResponse->objectType->name ?? '',
                    $dxaResponse->cadastral_number,
                    $dxaResponse->category_object_dictionary,
                    $dxaResponse->reestr_number,
                    $dxaResponse->number_protocol,
                    $dxaResponse->created_at,
                    $dxaResponse->status->status ?? '',
                    $dxaResponse->confirmed_at,
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
            'Obyekt turi',
            'Kadastr raqam',
            'Murakkablik toifasi',
            'Ekspertiza raqami',
            'Kengash raqami',
            'Ariza sanasi',
            'Status',
            'Javob berilgan sana',
        ];
    }
}
