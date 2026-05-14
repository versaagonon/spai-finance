@extends('layouts.finance')

@section('title', 'Semua Penyaluran')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Riwayat Penyaluran</h3>
        <a href="{{ route('finance.disbursements.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition shadow-sm">
            <i class="fas fa-minus"></i> Input Penyaluran
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4 font-medium">Tanggal</th>
                    <th class="p-4 font-medium">Penerima</th>
                    <th class="p-4 font-medium">Sumber Dana</th>
                    <th class="p-4 font-medium">Jumlah</th>
                    <th class="p-4 font-medium">Biaya Admin</th>
                    <th class="p-4 font-medium">Total Keluar</th>
                    <th class="p-4 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                @forelse($disbursements as $disbursement)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">{{ $disbursement->date->format('d M Y') }}</td>
                        <td class="p-4 font-medium">{{ $disbursement->recipient }}</td>
                        <td class="p-4 text-gray-500">{{ $disbursement->project->name ?? '-' }}</td>
                        <td class="p-4 font-bold text-red-600">Rp {{ number_format($disbursement->amount, 2, ',', '.') }}</td>
                        <td class="p-4 text-gray-500">Rp {{ number_format($disbursement->admin_fee, 2, ',', '.') }}</td>
                        <td class="p-4 font-bold text-gray-800">Rp {{ number_format($disbursement->amount + $disbursement->admin_fee, 2, ',', '.') }}</td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <a href="{{ route('finance.disbursements.edit', $disbursement) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('finance.disbursements.destroy', $disbursement) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">Belum ada data penyaluran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $disbursements->links() }}
    </div>
</div>
@endsection
