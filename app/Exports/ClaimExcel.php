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

    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    public function headings(): array
    {
        return [
            "T/R",
            "Ariza raqami",
            "Obyekt nomi",
            "Obyekt ariza raqami",
            "Viloyat",
            "Tuman",
            "Jami xonadon soni",
            "Bloklar soni",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
            "Topshirilgan honadon soni",
            "Topshirilgan bloklar soni",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
            "Umumiy maydoni tashqi (m2)",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
            "Topshirilgan umumiy foydalanish maydoni (m2)",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
            "Yashash maydoni (m2)",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
            "Qurilish osti maydoni (m2)",
            "Noturar",
            "Turar",
            "Yakka tartibdagi",
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if (!empty($this->tasks)) {
            return collect($this->tasks);
        } else {
            return collect([
                ['', '', '', '', '', '', '', '', '', '']
            ]);
        }
    }

    public function columnFormats(): array
    {
        return [
            //'C' => '+### (##) ###-##-##',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as bold text.
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (
                AfterSheet $event
            ) {
                $event->sheet->autoSize();

                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(40);

                $event->sheet->getDelegate()->getStyle('A:AX')->getAlignment()->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

                $event->sheet->getStyle('H1:K1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => '66B2FF']]);
                $event->sheet->getStyle('M1:P1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'FFFF66']]);
                $event->sheet->getStyle('Q1:T1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'E0E0E0']]);
                $event->sheet->getStyle('U1:X1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'FFB266']]);
                $event->sheet->getStyle('Y1:AB1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'FF8000']]);
                $event->sheet->getStyle('AC1:AF1')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'CCFFCC']]);


                $event->sheet->getDelegate()->getStyle('A1:AX1')->getAlignment()->setVertical(
                    Alignment::VERTICAL_CENTER
                );
            }
        ];
    }
}
