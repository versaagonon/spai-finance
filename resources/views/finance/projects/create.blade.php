@extends('layouts.finance')

@section('title', 'Tambah Proyek Baru')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Input Proyek Baru</h2>
    
    <form action="{{ route('finance.projects.store') }}" method="POST">
        @csrf
        <div class="space-y-6">
            
            <!-- Program Induk -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Program Induk</label>
                <select name="program_id" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
                    <option value="">-- Pilih Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Proyek ini berada di bawah naungan program apa?</p>
            </div>

            <!-- Nama Proyek -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Proyek</label>
                <input type="text" name="name" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Contoh: Bantuan Medis Gaza Tahap 1" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pilar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilar (Kategori)</label>
                    <select name="pilar" id="pilarSelect" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" onchange="toggleCustomPilar(this.value)">
                        @foreach($pillars as $pilar)
                            <option value="{{ $pilar }}">{{ $pilar }}</option>
                        @endforeach
                        <option value="Lainnya">-- Lainnya (Input Manual) --</option>
                    </select>
                </div>

                <div id="customPilarWrapper" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pilar Kustom</label>
                    <input type="text" name="pilar_custom" id="pilar_custom" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Masukkan nama pilar baru...">
                </div>

                <script>
                    function toggleCustomPilar(value) {
                        const wrapper = document.getElementById('customPilarWrapper');
                        const customInput = document.getElementById('pilar_custom');
                        if (value === 'Lainnya') {
                            wrapper.classList.remove('hidden');
                            customInput.required = true;
                            customInput.focus();
                        } else {
                            wrapper.classList.add('hidden');
                            customInput.required = false;
                        }
                    }
                </script>

                <!-- Target Dana -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Dana (Opsional)</label>
                    <input type="number" name="target_amount" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="0">
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Proyek</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Detail mengenai proyek ini..."></textarea>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('finance.projects.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg">Simpan Proyek</button>
        </div>
    </form>
</div>
@endsection
