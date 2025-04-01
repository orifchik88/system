<?php

namespace App\Exports;

use App\Models\DxaResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DxaResponsesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }
    public function query()
    {
        $query = DB::table('dxa_responses as dx')
            ->select([
                'dx.task_id as ariza_raqami',
                DB::raw(($this->filters['type'] ?? null) == 2 ? 'dx.old_task_id as eski_ariza_raqami' : 'NULL as eski_ariza_raqami'),
                'dx.object_name as obyekt_nomi',
                'r.name_uz as viloyat',
                'd.name_uz as tuman',
                DB::raw("CONCAT(u.surname, ' ', u.name, ' ', u.middle_name) as inspektor"),
                'ad.status as inspektor_tanlagan',
                'dx.inspector_commit as inspektor_yozgan_comment',
                'dx.category_object_dictionary as murakkablik_toifasi',
                'dx.number_protocol as kengash_xulosasi',
                'dx.reestr_number as expertiza',
                'dx.created_at as ariza_kelib_tushgan_sana',
                'dx.inspector_sent_at as inspektorga_yuborilgan_sana',
                'dx.inspector_answered_at as inpektor_registratorga_yuborgan_sana',
                'dx.confirmed_at as arizaga_javob_berilgan_sana',
                'dx.rejected_at as arizaga_rad_berilgan_sana',
                DB::raw("CASE
                    WHEN dx.confirmed_at > dx.deadline OR dx.rejected_at > dx.deadline THEN 'Ha'
                    ELSE 'Yo‘q'
                END as muddat_buzilishi"),
                'dxs.status as status',
                'dx.rejection_comment as rad_berilgan_sana',
                'dx.cost as quirilish_montaj_qiymati',
                'dx.price_supervision_service as gasn_summasi',
                'sp.name_uz as soha',
                'pr.name_uz as dastur',
                'fs.name as moliyalashtirish_manbai',
                'dx.cadastral_number as kadastr',
                'dxrb.organization_name as buyurtmachi_name',
                'dxrb.identification_number as buyurtmachi_inn',
                'dxrl.organization_name as loyihachi_name',
                'dxrl.identification_number as loyihachi_inn',
                'dxrq.organization_name as qurilish_name',
                'dxrq.identification_number as qurilish_inn'
            ])
            ->leftJoin('regions as r', 'dx.region_id', '=', 'r.id')
            ->leftJoin('districts as d', 'dx.district_id', '=', 'd.id')
            ->leftJoin('users as u', 'dx.inspector_id', '=', 'u.id')
            ->leftJoin('dxa_response_statuses as dxs', 'dx.dxa_response_status_id', '=', 'dxs.id')
            ->leftJoin('funding_sources as fs', 'dx.funding_source_id', '=', 'fs.id')
            ->leftJoin('spheres as sp', 'dx.sphere_id', '=', 'sp.id')
            ->leftJoin('programs as pr', 'dx.program_id', '=', 'pr.id')
            ->leftJoin('administrative_statuses as ad', 'dx.administrative_status_id', '=', 'ad.id')

            ->leftJoin(DB::raw("
                LATERAL (
                    SELECT organization_name, identification_number
                    FROM dxa_response_supervisors
                    WHERE dxa_response_id = dx.id AND role_id = 8
                    ORDER BY id LIMIT 1
                ) dxrb ON TRUE
            "), function ($join) {})

            ->leftJoin(DB::raw("
                LATERAL (
                    SELECT organization_name, identification_number
                    FROM dxa_response_supervisors
                    WHERE dxa_response_id = dx.id AND role_id = 9
                    ORDER BY id LIMIT 1
                ) dxrl ON TRUE
            "), function ($join) {})

            ->leftJoin(DB::raw("
                LATERAL (
                    SELECT organization_name, identification_number
                    FROM dxa_response_supervisors
                    WHERE dxa_response_id = dx.id AND role_id = 10
                    ORDER BY id LIMIT 1
                ) dxrq ON TRUE
            "), function ($join) {});

        if (!empty($this->filters['type'])) {
            $query->where('dx.notification_type', $this->filters['type']);
        }
        if (!empty($this->filters['region_id'])) {
            $query->where('dx.region_id', $this->filters['region_id']);
        }
        if (!empty($this->filters['district_id'])) {
            $query->where('dx.district_id', $this->filters['district_id']);
        }
        if (!empty($this->filters['inspector'])) {
            $query->where('dx.inspector_id', $this->filters['inspector']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('dxs.status', $this->filters['status']);
        }
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('dx.created_at', [$this->filters['start_date'], $this->filters['end_date']]);
        }

        return $query->orderBy('dx.created_at', 'DESC');
    }
    public function headings(): array
    {
        $headings = [
            'Ariza raqami',
        ];

        if (($this->filters['type'] ?? null) == 2) {
            $headings[] = 'Eski ariza raqami';
        }

        $headings = array_merge($headings, [
            'Obyekt nomi', 'Viloyat', 'Tuman', 'Inspektor',
            'Inspektor tanlagan', 'Inspektor yozgan comment', 'Murakkablik toifasi',
            'Kengash xulosasi', 'Expertiza', 'Ariza kelib tushgan sana',
            'Inspektorga yuborilgan sana', 'Inspektor registratorga yuborgan sana',
            'Arizaga javob berilgan sana', 'Muddat buzilishi', 'Status',
            'Rad berilgan sana', 'Qurilish-montaj qiymati', 'GASN summasi',
            'Soha', 'Dastur', 'Moliyalashtirish manbai', 'Kadastr',
            'Buyurtmachi nomi', 'Buyurtmachi INN', 'Loyihachi nomi', 'Loyihachi INN',
            'Qurilish nomi', 'Qurilish INN'
        ]);

        return $headings;
    }

    public function map($row): array
    {
        $data = [
            $row->ariza_raqami,
        ];

        if (($this->filters['type'] ?? null) == 2) {
            $data[] = $row->eski_ariza_raqami;
        }

        return array_merge($data, [
            $row->obyekt_nomi, $row->viloyat, $row->tuman, $row->inspektor,
            $row->inspektor_tanlagan, $row->inspektor_yozgan_comment, $row->murakkablik_toifasi,
            $row->kengash_xulosasi, $row->expertiza, $row->ariza_kelib_tushgan_sana,
            $row->inspektorga_yuborilgan_sana, $row->inpektor_registratorga_yuborgan_sana,
            $row->arizaga_javob_berilgan_sana, $row->muddat_buzilishi, $row->status,
            $row->rad_berilgan_sana, $row->qurilish_montaj_qiymati, $row->gasn_summasi,
            $row->soha, $row->dastur, $row->moliyalashtirish_manbai, $row->kadastr
        ]);
    }

}
