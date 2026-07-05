@extends('layouts.finance')

@section('title', 'Financial Overview')

@section('content')
    <!-- Date Filter & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <form method="GET" action="{{ route('finance.dashboard') }}" class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 shadow-sm">
            <i class="fas fa-calendar text-gray-400 ml-2"></i>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-sm border-none focus:ring-0 text-gray-600">
            <span class="text-gray-400">-</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-sm border-none focus:ring-0 text-gray-600">
            <button type="submit" class="bg-gray-100 px-3 py-1 rounded text-sm hover:bg-gray-200 text-gray-600">Filter</button>
        </form>

        <div class="flex gap-2">
            @if(auth()->user()->role === 'admin')
            <form action="{{ route('finance.bulk_delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dalam rentang tanggal ini?');" class="inline">
                @csrf
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-600 transition shadow-sm">
                    <i class="fas fa-trash-alt"></i> Hapus Data
                </button>
            </form>
             <a href="{{ route('finance.export_excel', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition shadow-sm flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Ekspor Excel
            </a>
            @endif
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Himpunan -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-green-50 rounded-full text-green-600">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Himpunan</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($totalHimpunan, 2, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Total Penyaluran -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                <i class="fas fa-hand-holding-heart text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Penyaluran</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($totalPenyaluran, 2, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Sisa Saldo Hak Amil -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-purple-50 rounded-full text-purple-600">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Sisa Saldo Hak Amil</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($sisaSaldoHakAmil, 2, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Dana Kelola -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-orange-50 rounded-full text-orange-600">
                <i class="fas fa-sync text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Dana Kelola</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($danaKelola, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Total Penyaluran per Pilar</h3>
        <div class="h-64">
            <canvas id="penyaluranChart"></canvas>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Program / Projects ({{ count($projects) }})</h3>
            <div class="relative">
                <input type="text" id="SearchProject" placeholder="Cari program..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-medium">Nama Program</th>
                        <th class="p-4 font-medium">Total Uang Masuk</th>
                        <th class="p-4 font-medium">Total Uang Keluar</th>
                        <th class="p-4 font-medium">Total Hak Amil</th>
                        <th class="p-4 font-medium">Sisa Saldo</th>
                        @if(auth()->user()->role === 'admin')
                        <th class="p-4 font-medium text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="projectTableBody"class="text-sm text-gray-700 divide-y divide-gray-100">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50 transition project-row">
                        <td class="p-4 font-medium project-name">{{ $project->name }}</td>
                        <td class="p-4">Rp {{ number_format($project->total_income, 2, ',', '.') }}</td>
                        <td class="p-4">Rp {{ number_format($project->total_expense, 2, ',', '.') }}</td>
                        <td class="p-4">Rp {{ number_format($project->total_amil, 2, ',', '.') }}</td>
                        <td class="p-4 font-bold {{ $project->sisa_saldo < 0 ? 'text-red-500' : 'text-green-600' }}">
                            Rp {{ number_format($project->sisa_saldo, 2, ',', '.') }}
                        </td>
                        @if(auth()->user()->role === 'admin')
                        <td class="p-4 text-center">
                            <a href="{{ route('finance.projects.show', $project->id) }}" class="text-gray-400 hover:text-green-600 transition">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}" class="p-8 text-center text-gray-400">Belum ada data program.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('penyaluranChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($penyaluranPerPilar->keys()) !!},
            datasets: [{
                label: 'Total Penyaluran (Rp)',
                data: {!! json_encode($penyaluranPerPilar->values()) !!},
                backgroundColor: '#3B82F6',
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal Layout like image
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    //fitur baru pencarioan table 
    document.getElementById('SearchProject').addEventListener('keyup', function() { 
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#projectTableBody .project-row');

        rows.forEach(row => {
            let nameCell = row.querySelector('.project-name');
            if (nameCell) {
                let nameText = nameCell.textContent || nameCell.innerText;
                if (nameText.toLowerCase().indexOf(filter) > -1 ) {
                } else {
                    row.style.display = "none";
                }
            }
        });
    });
</script>
@endpush
