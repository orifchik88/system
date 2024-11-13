<?php

namespace App\Exports;

use App\Enums\UserRoleEnum;
use App\Models\DxaResponse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DxaResponseExport implements FromCollection, WithHeadings, WithMapping
{

    public function __construct(protected int $id)
    {
    }

    public function collection()
    {
        return DxaResponse::where('id', $this->id)->get();
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

    public function map($dxaResponse): array
    {
        return [
            $dxaResponse->task_id,
            $dxaResponse->organization_name,
            $dxaResponse->supervisors()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->first()->identification_number,
            $dxaResponse->object_name,
            $dxaResponse->region->name_uz,
            $dxaResponse->district->name_uz,
            $dxaResponse->objectType->name,
            $dxaResponse->cadastral_number,
            $dxaResponse->category_object_dictionary,
            $dxaResponse->reestr_number,
            $dxaResponse->number_protocol,
            $dxaResponse->created_at,
            $dxaResponse->status->status,
            $dxaResponse->confirmed_at,
        ];
    }
}
