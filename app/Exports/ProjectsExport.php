<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Project::with(['donations', 'disbursements'])
            ->get()
            ->map(function ($project) {
                $donations = $project->donations();
                $disbursements = $project->disbursements();

                if ($this->startDate) {
                    $donations->whereDate('date', '>=', $this->startDate);
                    $disbursements->whereDate('date', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $donations->whereDate('date', '<=', $this->endDate);
                    $disbursements->whereDate('date', '<=', $this->endDate);
                }

                $totalIncome = $donations->sum('amount');
                $totalExpense = $disbursements->sum('amount');
                $totalAmil = $donations->sum('amil_amount');
                $sisaSaldo = ($totalIncome - $totalAmil) - $totalExpense;

                $project->total_income_calculated = $totalIncome;
                $project->total_expense_calculated = $totalExpense;
                $project->total_amil_calculated = $totalAmil;
                $project->sisa_saldo_calculated = $sisaSaldo;

                return $project;
            });
    }

    public function headings(): array
    {
        return [
            ['LAPORAN KEUANGAN SPAI'],
            ['Periode: ' . ($this->startDate ?: 'Keseluruhan') . ' s/d ' . ($this->endDate ?: 'Saat Ini')],
            [],
            [
                'No',
                'Nama Program / Proyek',
                'Total Uang Masuk',
                'Total Uang Keluar',
                'Total Hak Amil',
                'Sisa Saldo Netto'
            ]
        ];
    }

    public function map($project): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $project->name,
            $project->total_income_calculated,
            $project->total_expense_calculated,
            $project->total_amil_calculated,
            $project->sisa_saldo_calculated,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            4 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF16A34A'], // Green-600 Tailwind
                ],
            ],
            // Formatting currency for columns C to F starting from row 5
            'C5:F1000' => [
                'numberFormat' => [
                    'formatCode' => '#,##0.00_-'
                ],
            ],
        ];
    }
}
