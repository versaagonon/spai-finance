@extends('layouts.finance')

@section('title', 'Semua Program')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Portofolio Proyek</h3>
        <a href="{{ route('finance.projects.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition shadow-sm">
            <i class="fas fa-plus"></i> Tambah Proyek
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium">Nama Proyek</th>
                    <th class="p-4 font-medium">Program Induk</th>
                    <th class="p-4 font-medium">Pilar</th>
                    <th class="p-4 font-medium">Total Uang Masuk</th>
                    <th class="p-4 font-medium">Total Uang Keluar</th>
                    <th class="p-4 font-medium">Total Hak Amil</th>
                    <th class="p-4 font-medium">Sisa Saldo</th>
                    <th class="p-4 font-medium text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
            @forelse($projects as $project)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 font-medium">{{ $project->name }}</td>
                    <td class="p-4 text-gray-500">{{ $project->program->name ?? '-' }}</td>
                    <td class="p-4"><span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">{{ $project->pilar }}</span></td>
                    <td class="p-4">Rp {{ number_format($project->total_income, 2, ',', '.') }}</td>
                    <td class="p-4">Rp {{ number_format($project->total_expense, 2, ',', '.') }}</td>
                    <td class="p-4">Rp {{ number_format($project->total_amil, 2, ',', '.') }}</td>
                    <td class="p-4 font-bold {{ $project->sisa_saldo < 0 ? 'text-red-500' : 'text-green-600' }}">
                        Rp {{ number_format($project->sisa_saldo, 2, ',', '.') }}
                    </td>
                    <td class="p-4 text-center">
                        <a href="{{ route('finance.projects.show', $project->id) }}" class="text-blue-500 hover:text-blue-700 transition">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">Belum ada data program.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
