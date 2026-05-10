<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Disbursement;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // Date Filtering Logic
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $donationsQuery = Donation::query();
        $disbursementsQuery = Disbursement::query();

        if ($startDate && $endDate) {
            $donationsQuery->whereBetween('date', [$startDate, $endDate]);
            $disbursementsQuery->whereBetween('date', [$startDate, $endDate]);
        }

        // --- Core Statistics Calculation ---

        // 1. Total Himpunan (Total Income)
        $totalHimpunan = $donationsQuery->sum('amount');

        // 2. Total Hak Amil (Total Operational Fee)
        $totalHakAmil = $donationsQuery->sum('amil_amount');

        // 3. Total Penyaluran (Total Disbursement + Admin Fees)
        $totalPenyaluran = $disbursementsQuery->sum('amount') + $disbursementsQuery->sum('admin_fee');

        // 4. Dana Kelola (Managed Fund)
        $danaKelola = $totalHimpunan - $totalHakAmil - $totalPenyaluran;

        // 5. Sisa Saldo Hak Amil
        $operasionalDisbursements = (clone $disbursementsQuery)->whereHas('project', function($q) {
            $q->where('name', 'LIKE', 'SPAI%');
        })->get()->sum(function($d) {
            return $d->amount + $d->admin_fee;
        });
        $sisaSaldoHakAmil = $totalHakAmil - $operasionalDisbursements;


        // --- Chart Data: Penyaluran per Pilar ---
        $penyaluranPerPilar = DB::table('disbursements')
            ->join('projects', 'disbursements.project_id', '=', 'projects.id')
            ->select('projects.pilar', DB::raw('SUM(disbursements.amount + disbursements.admin_fee) as total'))
            ->groupBy('projects.pilar')
            ->pluck('total', 'projects.pilar');


        // --- Project Table Data (Filtered) ---
        $projects = Project::withSum(['donations as donations_sum_amount' => function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) $q->whereBetween('date', [$startDate, $endDate]);
        }], 'amount')
        ->withSum(['donations as donations_sum_amil_amount' => function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) $q->whereBetween('date', [$startDate, $endDate]);
        }], 'amil_amount')
        ->withSum(['disbursements as disbursements_sum_amount' => function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) $q->whereBetween('date', [$startDate, $endDate]);
        }], 'amount')
        ->withSum(['disbursements as disbursements_sum_admin_fee' => function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) $q->whereBetween('date', [$startDate, $endDate]);
        }], 'admin_fee')
        ->get()
        ->map(function ($project) use ($totalHakAmil) {
            // Accessors in Project.php will now automatically pick up these sums
            $project->total_income = $project->total_income ?? 0;
            $project->total_amil = $project->total_amil ?? 0;
            $project->total_expense = $project->total_expense ?? 0;
            
            if (trim(strtoupper($project->name)) === 'SPAI') {
                // Untuk proyek SPAI, Hak Amil yang muncul adalah Total Amil Global
                $project->total_amil = $totalHakAmil;
                $project->sisa_saldo = $totalHakAmil - $project->total_expense;
            } else {
                $project->sisa_saldo = ($project->total_income - $project->total_amil) - $project->total_expense;
            }
            return $project;
        });

        return view('dashboard.finance', compact(
            'totalHimpunan',
            'totalPenyaluran',
            'sisaSaldoHakAmil',
            'danaKelola',
            'penyaluranPerPilar',
            'projects'
        ));
    }

    // --- Donation Methods ---
    public function indexDonations()
    {
        $donations = Donation::with('project')->latest()->paginate(20);
        return view('finance.donations.index', compact('donations'));
    }

    public function createDonation()
    {
        $projects = Project::with('program')
            ->withSum('donations as total_income', 'amount')
            ->withSum('donations as total_amil', 'amil_amount')
            ->withSum('disbursements as total_expense', 'amount')
            ->get()
            ->map(function ($project) {
                $project->total_expense += $project->disbursements()->sum('admin_fee');
                $project->balance = ($project->total_income - $project->total_amil) - $project->total_expense;
                return $project;
            });
        $programs = \App\Models\Program::all();
        $globalAmil = \App\Models\AppSetting::get('global_amil_percentage', 0);
        
        $pillarsJson = \App\Models\AppSetting::get('project_pillars', json_encode(['Pendidikan', 'Kemanusiaan', 'Dakwah', 'Ekonomi', 'Kesehatan', 'Operasional', 'Lainnya']));
        $pillars = json_decode($pillarsJson, true);

        return view('finance.donations.create', compact('projects', 'programs', 'globalAmil', 'pillars'));
    }

    public function storeDonation(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'donor_name' => 'required|string',
            'amount' => 'required|numeric',
            'project_id' => 'required|exists:projects,id',
            'amil_percentage' => 'required|numeric',
            'bank_receiver' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Donation::create($validated);

        return redirect()->route('finance.dashboard')->with('success', 'Donation recorded successfully.');
    }

    // --- Disbursement Methods ---
    public function indexDisbursements()
    {
        $disbursements = Disbursement::with('project')->latest()->paginate(20);
        return view('finance.disbursements.index', compact('disbursements'));
    }

    public function createDisbursement()
    {
        $totalAmilGlobal = Donation::sum('amil_amount');

        $projects = Project::with('program')
            ->withSum('donations as total_income', 'amount')
            ->withSum('donations as total_amil', 'amil_amount')
            ->get()
            ->map(function ($project) use ($totalAmilGlobal) {
                // Ambil total pengeluaran termasuk biaya admin
                $project->total_expense = $project->disbursements()->sum('amount') + $project->disbursements()->sum('admin_fee');
                
                if (strtoupper($project->name) === 'SPAI') {
                    // Saldo SPAI = Total Hak Amil Global - Pengeluaran yang pakai proyek SPAI
                    $project->balance = $totalAmilGlobal - $project->total_expense;
                } else {
                    // Saldo Proyek Biasa = Dana Kelola (Bruto - Amil) - Pengeluaran Proyek
                    $project->balance = ($project->total_income - $project->total_amil) - $project->total_expense;
                }
                return $project;
            });
        $programs = \App\Models\Program::all();

        $pillarsJson = \App\Models\AppSetting::get('project_pillars', json_encode(['Pendidikan', 'Kemanusiaan', 'Dakwah', 'Ekonomi', 'Kesehatan', 'Operasional', 'Lainnya']));
        $pillars = json_decode($pillarsJson, true);

        return view('finance.disbursements.create', compact('projects', 'programs', 'pillars'));
    }

    public function storeDisbursement(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'recipient' => 'required|string',
            'amount' => 'required|numeric',
            'project_id' => 'required|exists:projects,id',
            'admin_fee' => 'nullable|numeric',
            'bank_sender' => 'nullable|string',
            'type' => 'required|string|in:project,amil',
            'description' => 'nullable|string',
        ]);

        Disbursement::create($validated);

        return redirect()->route('finance.dashboard')->with('success', 'Disbursement recorded successfully.');
    }

    // --- Project Ledger ---
    public function indexProjects()
    {
        $totalAmilGlobal = Donation::sum('amil_amount');

        $projects = Project::with('program')
            ->withSum('donations as donations_sum_amount', 'amount')
            ->withSum('donations as donations_sum_amil_amount', 'amil_amount')
            ->withSum('disbursements as disbursements_sum_amount', 'amount')
            ->withSum('disbursements as disbursements_sum_admin_fee', 'admin_fee')
            ->get()
            ->map(function ($project) use ($totalAmilGlobal) {
                $project->total_income = $project->total_income ?? 0;
                $project->total_amil = $project->total_amil ?? 0;
                $project->total_expense = $project->total_expense ?? 0;
                
                if (trim(strtoupper($project->name)) === 'SPAI') {
                    $project->total_amil = $totalAmilGlobal;
                    $project->sisa_saldo = $totalAmilGlobal - $project->total_expense;
                } else {
                    $project->sisa_saldo = ($project->total_income - $project->total_amil) - $project->total_expense;
                }
                return $project;
            });
            
        return view('finance.projects.index', compact('projects'));
    }

    public function createProject()
    {
        $programs = \App\Models\Program::all();
        $pillarsJson = \App\Models\AppSetting::get('project_pillars', json_encode(['Pendidikan', 'Kemanusiaan', 'Dakwah', 'Ekonomi', 'Kesehatan', 'Operasional', 'Lainnya']));
        $pillars = json_decode($pillarsJson, true);

        return view('finance.projects.create', compact('programs', 'pillars'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'pilar' => 'nullable|string',
            'pilar_custom' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        if ($validated['pilar'] === 'Lainnya' && !empty($validated['pilar_custom'])) {
            $validated['pilar'] = $validated['pilar_custom'];
        }

        Project::create($validated);

        return redirect()->route('finance.projects.index')->with('success', 'Proyek berhasil dibuat.');
    }

    public function showProject(Project $project)
    {
        $project->load(['donations', 'disbursements']);
        
        // Merge collections for chronological ledger
        $ledger = collect();
        
        foreach ($project->donations as $donation) {
            $ledger->push([
                'date' => $donation->date,
                'type' => 'Penerimaan',
                'description' => 'Donasi dari ' . $donation->donor_name,
                'debit' => 0,
                'credit' => $donation->managed_fund, // Income for project is Managed Fund (Net)
                'ref_id' => $donation->id
            ]);
        }

        foreach ($project->disbursements as $disbursement) {
            $ledger->push([
                'date' => $disbursement->date,
                'type' => 'Pengeluaran',
                'description' => 'Penyaluran kepada ' . $disbursement->recipient . ' (' . $disbursement->description . ')',
                'debit' => $disbursement->amount + $disbursement->admin_fee, // Expense is Amount + Admin Fee
                'credit' => 0,
                'ref_id' => $disbursement->id
            ]);
        }

        $ledger = $ledger->sortBy('date');

        return view('finance.projects.show', compact('project', 'ledger'));
    }

    // --- Utilities ---
    public function bulkDelete(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Please select a date range to delete.');
        }

        Donation::whereBetween('date', [$startDate, $endDate])->delete();
        Disbursement::whereBetween('date', [$startDate, $endDate])->delete();

        return redirect()->back()->with('success', 'Data deleted for the selected range.');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'Laporan_Keuangan_SPAI_' . date('Y-m-d') . '.xlsx';

        $projects = Project::with(['donations', 'disbursements'])->get()->map(function ($project) use ($startDate, $endDate) {
            $donations = $project->donations();
            $disbursements = $project->disbursements();

            if ($startDate) {
                $donations->whereDate('date', '>=', $startDate);
                $disbursements->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $donations->whereDate('date', '<=', $endDate);
                $disbursements->whereDate('date', '<=', $endDate);
            }

            $project->total_income_calc = $donations->sum('amount');
            $project->total_expense_calc = $disbursements->sum('amount') + $disbursements->sum('admin_fee');
            $project->total_amil_calc = $donations->sum('amil_amount');
            $project->sisa_saldo_calc = ($project->total_income_calc - $project->total_amil_calc) - $project->total_expense_calc;

            return $project;
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Judul Laporan
        $periodeText = 'Periode: ' . ($startDate ?: 'Awal') . ' s/d ' . ($endDate ?: 'Saat Ini');
        $sheet->setCellValue('A1', 'LAPORAN KEUANGAN SPAI');
        $sheet->setCellValue('A2', $periodeText);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        // Header Tabel
        $headers = ['No', 'Nama Program / Proyek', 'Total Uang Masuk', 'Total Uang Keluar', 'Total Hak Amil', 'Sisa Saldo Netto'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '4', $header);
            $columnIndex++;
        }

        // Style Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

        // Isi Data
        $row = 5;
        $no = 1;
        foreach ($projects as $project) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $project->name);
            $sheet->setCellValue('C' . $row, $project->total_income_calc);
            $sheet->setCellValue('D' . $row, $project->total_expense_calc);
            $sheet->setCellValue('E' . $row, $project->total_amil_calc);
            $sheet->setCellValue('F' . $row, $project->sisa_saldo_calc);
            
            // Format Rupiah
            $sheet->getStyle('C'.$row.':F'.$row)->getNumberFormat()->setFormatCode('"Rp "#,##0.00_-');
            $row++;
        }

        // Auto-size kolom
        foreach (range('A', 'F') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        // Stream output
        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function reportReceipts(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        $query = Donation::with('project')->whereYear('date', $year);
        $donations = $query->latest()->get();

        // 1. Pivot Logic (Annual Summary)
        $pivotReport = [];
        $monthlyTotals = array_fill(1, 12, 0);

        foreach ($donations as $donation) {
            $projectName = $donation->project ? $donation->project->name : 'Tanpa Proyek';
            $month = (int) date('m', strtotime($donation->date));
            $amount = $donation->amount;

            if (!isset($pivotReport[$projectName])) {
                $pivotReport[$projectName] = array_fill(1, 12, 0);
                $pivotReport[$projectName]['total_yearly'] = 0;
            }

            $pivotReport[$projectName][$month] += $amount;
            $pivotReport[$projectName]['total_yearly'] += $amount;
            $monthlyTotals[$month] += $amount;
        }

        $grandTotal = array_sum($monthlyTotals);
        ksort($pivotReport);

        // 2. Statistics Summary
        $totalAmount = $donations->sum('amount');
        $totalAmil = $donations->sum('amil_amount');
        $totalNet = $donations->sum('managed_fund');

        return view('finance.report.receipts', compact(
            'donations', 'totalAmount', 'totalAmil', 'totalNet', 'year',
            'pivotReport', 'monthlyTotals', 'grandTotal'
        ));
    }

    public function reportDisbursements(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        $query = Disbursement::with('project')->whereYear('date', $year);
        $disbursements = $query->latest()->get();

        // 1. Pivot Logic (Annual Summary)
        $pivotReport = [];
        $monthlyTotals = array_fill(1, 12, 0);

        foreach ($disbursements as $disbursement) {
            $projectName = $disbursement->project ? $disbursement->project->name : 'Tanpa Proyek';
            $month = (int) date('m', strtotime($disbursement->date));
            $amount = $disbursement->amount + $disbursement->admin_fee; // Total gross disbursement

            if (!isset($pivotReport[$projectName])) {
                $pivotReport[$projectName] = array_fill(1, 12, 0);
                $pivotReport[$projectName]['total_yearly'] = 0;
            }

            $pivotReport[$projectName][$month] += $amount;
            $pivotReport[$projectName]['total_yearly'] += $amount;
            $monthlyTotals[$month] += $amount;
        }

        $grandTotal = array_sum($monthlyTotals);
        ksort($pivotReport);

        // 2. Statistics Summary
        $totalAmount = $disbursements->sum('amount');
        $totalFee = $disbursements->sum('admin_fee');
        $totalGross = $totalAmount + $totalFee;

        return view('finance.report.disbursements', compact(
            'disbursements', 'totalAmount', 'totalFee', 'totalGross', 'year',
            'pivotReport', 'monthlyTotals', 'grandTotal'
        ));
    }

    public function reportAmil(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Ambil data donasi untuk tahun yang dipilih
        $donations = Donation::with('project')
            ->whereYear('date', $year)
            ->get();

        // 1. Logika Perhitungan SPAI Terintegrasi (Rumus Klien)
        $totalHimpunan = $donations->sum('amount');
        $totalHakAmilGlobal = $donations->sum('amil_amount');
        
        // Dana masuk SPAI = Total Hak Amil dari semua proyek
        $totalMasukFormula = $totalHakAmilGlobal; 

        // Ambil pengeluaran tahun ini
        $disbursements = Disbursement::with('project')->whereYear('date', $year)->get();
        
        // total_hak_amil = Semua pengeluaran yang menggunakan Proyek "SPAI"
        $totalHakAmilKeluar = $disbursements->filter(function($d) {
            return $d->project && strtoupper($d->project->name) === 'SPAI';
        })->sum('amount') + $disbursements->filter(function($d) {
            return $d->project && strtoupper($d->project->name) === 'SPAI';
        })->sum('admin_fee');
        
        // Sisa Saldo SPAI = Total Hak Amil - Pengeluaran Operasional SPAI
        $sisaSaldo = $totalMasukFormula - $totalHakAmilKeluar;

        // 2. Logika Laporan Pivot Hak Amil (Sesuai format klien)
        $report = [];
        $monthlyTotals = array_fill(1, 12, 0);

        foreach ($donations as $donation) {
            $projectName = $donation->project ? $donation->project->name : 'Tanpa Proyek';
            $month = (int) date('m', strtotime($donation->date));
            $amilAmount = $donation->amil_amount;

            if (!isset($report[$projectName])) {
                $report[$projectName] = array_fill(1, 12, 0);
                $report[$projectName]['total_yearly'] = 0;
            }

            $report[$projectName][$month] += $amilAmount;
            $report[$projectName]['total_yearly'] += $amilAmount;
            
            $monthlyTotals[$month] += $amilAmount;
        }

        $grandTotal = array_sum($monthlyTotals);
        ksort($report);

        return view('finance.report.amil', compact(
            'totalHimpunan',
            'totalHakAmilGlobal',
            'totalMasukFormula',
            'totalHakAmilKeluar',
            'sisaSaldo',
            'report',
            'monthlyTotals',
            'grandTotal',
            'year'
        ));
    }
}
