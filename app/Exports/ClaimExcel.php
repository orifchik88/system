<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClaimExcel implements FromCollection, WithColumnFormatting, WithHeadings, WithStyles, WithEvents
{
    private array $tasks;

    public function __construct(array $tasks) {
        $this->tasks = $tasks;
    }

    public function headings(): array
    {
        return [
            "ARIZA RAQAMI",
            "Blok turi",
            "Xonadonlar soni",
            "Viloyat",
            "Tuman",
            "Yashash maydoni ",
            "Umumiy foydalanish maydoni",
            "Umumiy maydon",
            "Qurilish osti maydoni",
            "Obyekt raqami",
            "Javob berilgan sana"
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if(!empty($this->tasks)) {
            return collect($this->tasks);
        } else {
            return collect([
                ['','','','','','','','','','']
            ]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'C' => '+### (##) ###-##-##',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as bold text.
            1  => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(
                AfterSheet $event
            ) {
                $event->sheet->autoSize();

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(40);

                $event->sheet->getDelegate()->getStyle('A:J')->getAlignment()->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

                $event->sheet->getDelegate()->getStyle('A1:J1')->getAlignment()->setVertical(
                    Alignment::VERTICAL_CENTER
                );
            }
        ];
    }
}
