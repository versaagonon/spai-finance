@extends('layouts.finance')

@section('title', 'Tambah Program Baru')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Input Program Induk</h2>
    
    <form action="{{ route('finance.programs.store') }}" method="POST">
        @csrf
        <div class="space-y-6">
            
            <!-- Nama Program -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Program</label>
                <input type="text" name="name" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="Contoh: Satu Hati untuk Palestina" required>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="Deskripsi singkat mengenai program ini..."></textarea>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('finance.programs.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-lg">Simpan Program</button>
        </div>
    </form>
</div>
@endsection
