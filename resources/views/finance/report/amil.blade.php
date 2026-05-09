@extends('layouts.finance')

@section('title', 'Laporan Hak Amil & Saldo Sistem')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('finance.report.amil') }}" class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
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

<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
    <div class="p-8 bg-gradient-to-br from-green-600 to-green-800 text-white">
        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
            <i class="fas fa-calculator"></i> Ringkasan Saldo Sistem (SPAI)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <p class="text-green-100 text-sm mb-1 uppercase tracking-wider font-semibold">Total Hak Amil</p>
                <p class="text-3xl font-black">Rp {{ number_format($totalMasukFormula, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-green-100 text-sm mb-1 uppercase tracking-wider font-semibold">Total Pengeluaran SPAI</p>
                <p class="text-3xl font-black">Rp {{ number_format($totalHakAmilKeluar, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white/10 p-4 rounded-xl border border-white/20">
                <p class="text-white text-sm mb-1 uppercase tracking-wider font-semibold">Sisa Saldo Netto</p>
                <p class="text-3xl font-black text-yellow-300">Rp {{ number_format($sisaSaldo, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    
    <div class="p-8 border-t border-gray-100 bg-blue-50/30">
        <div class="flex items-start gap-4">
            <div class="bg-blue-100 p-3 rounded-lg">
                <i class="fas fa-info-circle text-blue-600"></i>
            </div>
            <div>
                <h4 class="text-blue-800 font-bold mb-1">Informasi Integrasi</h4>
                <p class="text-sm text-blue-600 leading-relaxed">
                    Saldo Program SPAI dihitung secara otomatis dari akumulasi Hak Amil seluruh proyek. 
                    Setiap pengeluaran operasional (gaji, dsb) dapat diinput melalui menu Penyaluran dengan memilih <strong>Proyek: SPAI</strong>.
                </p>
            </div>
        </div>
    </div>
    
    <div class="p-8">
        <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-table text-blue-600"></i> Laporan Hak Amil per Proyek ({{ $year }})
        </h4>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-green-50 text-green-800 text-xs uppercase tracking-wider border-y border-green-200">
                        <th class="px-4 py-3 font-bold border-x border-green-200">NO</th>
                        <th class="px-4 py-3 font-bold border-r border-green-200">Project</th>
                        @php
                            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        @endphp
                        @foreach($months as $month)
                            <th class="px-4 py-3 font-bold border-r border-green-200">{{ $month }}</th>
                        @endforeach
                        <th class="px-4 py-3 font-bold bg-green-100 border-r border-green-200">TOTAL HAK AMIL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @php $no = 1; @endphp
                    @forelse($report as $project => $data)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 border-x border-gray-100 text-center">{{ $no++ }}</td>
                            <td class="px-4 py-3 border-r border-gray-100 font-medium text-gray-800">{{ $project }}</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="px-4 py-3 border-r border-gray-100 text-right text-gray-600">
                                    {{ $data[$m] > 0 ? 'Rp ' . number_format($data[$m], 0, ',', '.') : 'Rp 0' }}
                                </td>
                            @endfor
                            <td class="px-4 py-3 border-r border-gray-100 text-right font-bold text-green-700 bg-green-50/50">
                                Rp {{ number_format($data['total_yearly'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-8 text-center text-gray-500 border-x border-gray-100">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                    <p>Belum ada data hak amil untuk tahun {{ $year }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($report) > 0)
                <tfoot>
                    <tr class="bg-green-600 text-white font-bold text-sm">
                        <td colspan="2" class="px-4 py-4 border-x border-green-700 text-right uppercase tracking-wider">
                            Total Penerimaan Hak Amil Per Bulan
                        </td>
                        @for($m = 1; $m <= 12; $m++)
                            <td class="px-4 py-4 border-r border-green-700 text-right">
                                {{ $monthlyTotals[$m] > 0 ? 'Rp ' . number_format($monthlyTotals[$m], 0, ',', '.') : 'Rp 0' }}
                            </td>
                        @endfor
                        <td class="px-4 py-4 border-r border-green-700 text-right bg-green-700">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        aside, header, form, button, .mb-8:first-child {
            display: none !important;
        }
        body { background: white !important; }
        .shadow-lg, .border { border: none !important; box-shadow: none !important; }
        .rounded-2xl { border-radius: 0 !important; }
        .bg-gradient-to-br { background: #166534 !important; color: white !important; }
    }
</style>
@endsection
