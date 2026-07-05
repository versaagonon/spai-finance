@extends('layouts.finance')

@section('title', 'Manajemen Program & Proyek')

@section('content')
<div class="space-y-8">
    
    <!-- Header & Add Program -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Portofolio Program & Proyek</h2>
            <p class="text-sm text-gray-500">Kelola semua program dan proyek turunannya di satu tempat.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('finance.projects.create') }}" class="bg-white border border-indigo-600 text-indigo-600 px-5 py-2.5 rounded-lg text-sm hover:bg-indigo-50 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Proyek
            </a>
            <a href="{{ route('finance.programs.create') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-layer-group"></i> Tambah Program
            </a>
        </div>
    </div>

    <!-- Programs List -->
    @forelse($programs as $program)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Program Header -->
        <div class="bg-gray-50 p-6 border-b border-gray-200 flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h3 class="text-lg font-bold text-gray-800">{{ $program->name }}</h3>
                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full font-bold">{{ $program->projects->count() }} Proyek</span>
                </div>
                <p class="text-sm text-gray-600 max-w-2xl">{{ $program->description ?? 'Tidak ada deskripsi.' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('finance.programs.edit', $program->id) }}" class="text-gray-400 hover:text-blue-600 transition p-2" title="Edit Program">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('finance.programs.destroy', $program->id) }}" method="POST" onsubmit="return confirm('Hapus program ini? Proyek akan kehilangan induknya.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition p-2" title="Hapus Program">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Projects Table (Nested) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-8 font-medium w-1/4">Nama Proyek</th>
                        <th class="p-4 font-medium">Pilar</th>
                        <th class="p-4 font-medium">Uang Masuk</th>
                        <th class="p-4 font-medium">Uang Keluar</th>
                        <th class="p-4 font-medium">Saldo</th>
                        <th class="p-4 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                    @forelse($program->projects as $project)
                        @php
                            $total_expense = $project->total_expense + $project->total_admin_fee;
                            $net_balance = ($project->total_income - $project->total_amil) - $total_expense;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 pl-8 font-medium text-gray-800">
                                <a href="{{ route('finance.projects.show', $project->id) }}" class="hover:text-green-600 hover:underline">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td class="p-4">
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs border border-gray-200">{{ $project->pilar ?? '-' }}</span>
                            </td>
                            <td class="p-4 text-green-600">Rp {{ number_format($project->total_income, 0, ',', '.') }}</td>
                            <td class="p-4 text-red-600">Rp {{ number_format($total_expense, 0, ',', '.') }}</td>
                            <td class="p-4 font-bold {{ $net_balance < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                Rp {{ number_format($net_balance, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-center flex justify-center items-center gap-2">
                                <a href="{{ route('finance.projects.show', $project->id) }}" class="text-green-600 hover:text-green-800 text-xs font-medium border border-green-200 bg-green-50 px-3 py-1.5 rounded-lg transition">
                                    Lihat Detail
                                </a>
                                <form action="{{ route('finance.projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium border border-red-200 bg-red-50 px-3 py-1.5 rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm bg-gray-50 italic">
                                Belum ada proyek di program ini. 
                                <a href="{{ route('finance.projects.create') }}" class="text-green-600 hover:underline">Tambah Proyek Baru</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="p-12 text-center bg-white rounded-xl border border-gray-200">
        <div class="inline-block p-4 rounded-full bg-gray-100 text-gray-400 mb-4">
            <i class="fas fa-layer-group text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">Belum ada Program</h3>
        <p class="text-gray-500 mb-6">Mulai dengan membuat Program Induk pertama Anda.</p>
        <a href="{{ route('finance.programs.create') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
            Buat Program Baru
        </a>
    </div>
    @endforelse

    <!-- Orphaned Projects Section -->
    @if($noProgramProjects->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8 border-l-4 border-l-gray-400">
        <div class="bg-gray-50 p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-gray-400"></i>
                <h3 class="text-lg font-bold text-gray-800">Proyek Tanpa Program Induk (Lainnya)</h3>
            </div>
            <p class="text-sm text-gray-500 mt-1">Proyek-proyek ini berjalan secara mandiri atau belum dikategorikan.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <tbody class="text-sm text-gray-700 divide-y divide-gray-50">
                    @foreach($noProgramProjects as $project)
                        @php
                            $total_expense = $project->total_expense + $project->total_admin_fee;
                            $net_balance = ($project->total_income - $project->total_amil) - $total_expense;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 pl-8 font-medium text-gray-800 w-1/4">
                                <a href="{{ route('finance.projects.show', $project->id) }}" class="hover:text-green-600 hover:underline">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td class="p-4">
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs border border-gray-200">{{ $project->pilar ?? '-' }}</span>
                            </td>
                            <td class="p-4 text-green-600">Rp {{ number_format($project->total_income, 0, ',', '.') }}</td>
                            <td class="p-4 text-red-600">Rp {{ number_format($total_expense, 0, ',', '.') }}</td>
                            <td class="p-4 font-bold {{ $net_balance < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                Rp {{ number_format($net_balance, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-center flex justify-center items-center gap-2">
                                <a href="{{ route('finance.projects.show', $project->id) }}" class="text-green-600 hover:text-green-800 text-xs font-medium border border-green-200 bg-green-50 px-3 py-1.5 rounded-lg transition">
                                    Lihat Detail
                                </a>
                                <form action="{{ route('finance.projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium border border-red-200 bg-red-50 px-3 py-1.5 rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="pt-4">
        {{ $programs->links() }}
    </div>
</div>
@endsection
