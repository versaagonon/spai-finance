@extends('layouts.finance')

@section('title', 'Buku Besar Program: ' . $project->name)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="p-6 border-b border-gray-100 bg-gray-50">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
                <span class="text-sm text-gray-500 bg-gray-200 px-2 py-1 rounded">{{ $project->pilar ?? 'General' }}</span>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Saldo Saat Ini</p>
                <h2 class="text-3xl font-bold {{ $project->balance < 0 ? 'text-red-500' : 'text-green-600' }}">
                    Rp {{ number_format($project->balance, 2, ',', '.') }}
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Transaksi</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium">Tanggal</th>
                    <th class="p-4 font-medium">Keterangan</th>
                    <th class="p-4 font-medium text-right text-red-600">Debit (Keluar)</th>
                    <th class="p-4 font-medium text-right text-green-600">Kredit (Masuk)</th>
                    <th class="p-4 font-medium text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                @php $runningBalance = 0; @endphp
                @forelse($ledger as $entry)
                    @php 
                        $runningBalance += ($entry['credit'] - $entry['debit']); 
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">{{ $entry['date']->format('d M Y') }}</td>
                        <td class="p-4">
                            <span class="block font-medium">{{ $entry['description'] }}</span>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">{{ $entry['type'] }}</span>
                        </td>
                        <td class="p-4 text-right text-red-600">
                            {{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 2, ',', '.') : '-' }}
                        </td>
                         <td class="p-4 text-right text-green-600">
                            {{ $entry['credit'] > 0 ? 'Rp ' . number_format($entry['credit'], 2, ',', '.') : '-' }}
                        </td>
                        <td class="p-4 text-right font-bold text-gray-800">
                            Rp {{ number_format($runningBalance, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">Belum ada transaksi untuk program ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
