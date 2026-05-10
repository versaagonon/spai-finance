@extends('layouts.finance')

@section('title', 'Pengaturan Finansial')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Hak Amil</h2>
        <p class="text-gray-500 text-sm">Atur persentase pemotongan hak amil secara sistematis.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Global Settings -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-globe text-blue-500"></i> Pengaturan Global
                </h3>
                <form action="{{ route('finance.settings.update_global') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Persentase Default (%)</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" name="global_amil_percentage" value="{{ $globalAmil }}" class="flex-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm">
                                    Simpan
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 italic">*Digunakan jika tidak ada pengaturan spesifik pada program atau proyek.</p>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-columns text-purple-500"></i> Daftar Pilar Proyek
                </h3>
                <form action="{{ route('finance.settings.update_pillars') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilar (Pisahkan dengan koma)</label>
                            <textarea name="pillars" rows="4" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm text-sm" placeholder="Contoh: Pendidikan, Kemanusiaan, Dakwah">{{ implode(', ', $pillars) }}</textarea>
                            <p class="text-xs text-gray-400 mt-2">Daftar ini akan muncul sebagai pilihan kategori saat membuat proyek baru.</p>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition shadow-sm font-medium">
                            Perbarui Daftar Pilar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                <h4 class="font-bold text-blue-800 text-sm mb-2">Informasi Hierarki</h4>
                <ul class="text-xs text-blue-700 space-y-2">
                    <li class="flex gap-2">
                        <span class="font-bold">1. Proyek:</span> Prioritas tertinggi jika toggle "Gunakan Spesifik" aktif.
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold">2. Program:</span> Digunakan jika proyek tidak punya setting spesifik.
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold">3. Global:</span> Digunakan sebagai nilai dasar terakhir.
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right: Program & Project Settings -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Per Program -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-layer-group text-green-600"></i> Persentase per Program
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($programs as $program)
                        <div class="p-4 hover:bg-gray-50 transition flex items-center justify-between gap-4">
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ $program->name }}</p>
                                <p class="text-xs text-gray-500">{{ $program->projects_count ?? 0 }} Proyek terkait</p>
                            </div>
                            <form action="{{ route('finance.settings.update_program', $program) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <div class="relative">
                                    <input type="number" step="0.1" name="amil_percentage" value="{{ $program->amil_percentage }}" class="w-24 pl-3 pr-8 py-1.5 text-sm rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                                </div>
                                <button type="submit" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Simpan">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Per Project -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-project-diagram text-orange-500"></i> Persentase per Proyek
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-widest border-b border-gray-100">
                                <th class="px-6 py-3 font-bold">Proyek & Program</th>
                                <th class="px-6 py-3 font-bold text-center">Gunakan Spesifik?</th>
                                <th class="px-6 py-3 font-bold">Persentase</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach($projects as $project)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-800">{{ $project->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium uppercase">{{ $project->program->name ?? 'Tanpa Program' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('finance.settings.update_project', $project) }}" method="POST" id="form-toggle-{{ $project->id }}">
                                            @csrf
                                            <input type="hidden" name="amil_percentage" value="{{ $project->amil_percentage }}">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="use_custom_amil" value="1" class="sr-only peer" {{ $project->use_custom_amil ? 'checked' : '' }} onchange="this.form.submit()">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('finance.settings.update_project', $project) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @if($project->use_custom_amil)
                                                <input type="hidden" name="use_custom_amil" value="1">
                                            @endif
                                            <div class="relative">
                                                <input type="number" step="0.1" name="amil_percentage" value="{{ $project->amil_percentage }}" class="w-20 pl-3 pr-7 py-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm text-sm" {{ !$project->use_custom_amil ? 'disabled' : '' }}>
                                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]">%</span>
                                            </div>
                                            <button type="submit" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition {{ !$project->use_custom_amil ? 'invisible' : '' }}">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
