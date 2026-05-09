@extends('layouts.finance')

@section('title', 'Laporan Penerimaan Donasi')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('finance.report.receipts') }}" class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt text-gray-400"></i>
                <select name="year" class="text-sm border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500">
                    @for($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                Filter
            </button>
        </form>

        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500 mb-1 font-semibold uppercase tracking-wider">Total Penerimaan Bruto ({{ $year }})</p>
        <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500 mb-1 font-semibold uppercase tracking-wider">Total Hak Amil (Fee)</p>
        <h3 class="text-2xl font-bold text-orange-600">Rp {{ number_format($totalAmil, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500 mb-1 font-semibold uppercase tracking-wider">Dana Kelola (Netto)</p>
        <h3 class="text-2xl font-bold text-green-600">Rp {{ number_format($totalNet, 0, ',', '.') }}</h3>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-table text-blue-600"></i> Rekap Tahunan Penerimaan per Proyek ({{ $year }})
        </h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-green-50 text-green-800 text-xs uppercase tracking-wider border-y border-green-200">
                    <th class="px-4 py-3 font-bold border-x border-green-200">NO</th>
                    <th class="px-4 py-3 font-bold border-r border-green-200">Project</th>
                    @php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']; @endphp
                    @foreach($months as $month)
                        <th class="px-4 py-3 font-bold border-r border-green-200">{{ $month }}</th>
                    @endforeach
                    <th class="px-4 py-3 font-bold bg-green-100 border-r border-green-200">TOTAL BRUTO</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @php $no = 1; @endphp
                @forelse($pivotReport as $project => $data)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 border-x border-gray-100 text-center">{{ $no++ }}</td>
                        <td class="px-4 py-3 border-r border-gray-100 font-medium text-gray-800">{{ $project }}</td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="px-4 py-3 border-r border-gray-100 text-right text-gray-600">
                                {{ $data[$m] > 0 ? number_format($data[$m], 0, ',', '.') : '-' }}
                            </td>
                        @endfor
                        <td class="px-4 py-3 border-r border-gray-100 text-right font-bold text-green-700 bg-green-50/50">
                            {{ number_format($data['total_yearly'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="px-6 py-8 text-center text-gray-500 border-x border-gray-100 italic">Belum ada data di tahun {{ $year }}</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($pivotReport) > 0)
            <tfoot class="bg-green-600 text-white font-bold text-xs">
                <tr>
                    <td colspan="2" class="px-4 py-3 border-x border-green-700 text-right uppercase tracking-wider">TOTAL PER BULAN</td>
                    @for($m = 1; $m <= 12; $m++)
                        <td class="px-4 py-3 border-r border-green-700 text-right">
                            {{ $monthlyTotals[$m] > 0 ? number_format($monthlyTotals[$m], 0, ',', '.') : '-' }}
                        </td>
                    @endfor
                    <td class="px-4 py-3 border-r border-green-700 text-right bg-green-700">
                        {{ number_format($grandTotal, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800">Rincian Transaksi Penerimaan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-bold">Tanggal</th>
                    <th class="px-6 py-4 font-bold">Donatur</th>
                    <th class="px-6 py-4 font-bold">Proyek</th>
                    <th class="px-6 py-4 font-bold text-right">Bruto</th>
                    <th class="px-6 py-4 font-bold text-right">Hak Amil</th>
                    <th class="px-6 py-4 font-bold text-right">Netto</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                @forelse ($donations as $donation)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">{{ $donation->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-medium">{{ $donation->donor_name }}</td>
                        <td class="px-6 py-4">{{ $donation->project->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-orange-600">{{ number_format($donation->amil_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-green-600 font-medium">Rp {{ number_format($donation->managed_fund, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        aside, header, form, button, .mb-8:first-child {
            display: none !important;
        }
        body { background: white !important; }
        .shadow-sm, .border { border: none !important; box-shadow: none !important; }
        .rounded-xl, .rounded-2xl { border-radius: 0 !important; }
    }
</style>
@endsection
