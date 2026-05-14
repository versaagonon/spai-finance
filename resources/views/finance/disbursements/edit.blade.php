@extends('layouts.finance')

@section('title', 'Edit Disbursement Entry')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Pengeluaran (Penyaluran)</h2>
    
    <form action="{{ route('finance.disbursements.update', $disbursement) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $disbursement->date->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm" required>
            </div>

            <!-- Penerima Manfaat -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Penerima Manfaat</label>
                <input type="text" name="recipient" value="{{ $disbursement->recipient }}" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm" placeholder="Nama Penerima / Vendor" required>
            </div>

            <!-- Bank Pengirim -->
            @php
                $standardBanks = ['BSI Main Account', 'BCA Operasional'];
                // Since the value saved might be just 'BSI' or 'BCA', check both
                $savedBank = $disbursement->bank_sender;
                $isCustomBank = !in_array($savedBank, $standardBanks) && !in_array($savedBank, ['BSI', 'BCA']);
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Pengirim</label>
                <select name="bank_sender" id="bank_sender" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm">
                    <option value="BSI" {{ $savedBank === 'BSI' || $savedBank === 'BSI Main Account' ? 'selected' : '' }}>BSI Main Account</option>
                    <option value="BCA" {{ $savedBank === 'BCA' || $savedBank === 'BCA Operasional' ? 'selected' : '' }}>BCA Operasional</option>
                    <option value="Lainnya" {{ $isCustomBank ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Bank Pengirim Lainnya -->
            <div id="bank_sender_custom_group" style="display: {{ $isCustomBank ? 'block' : 'none' }};">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank Lainnya</label>
                <input type="text" name="bank_sender_custom" value="{{ $isCustomBank ? $savedBank : '' }}" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm" placeholder="Masukkan nama bank">
            </div>

            <!-- Uang Keluar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Keluar (Rp)</label>
                <input type="number" name="amount" value="{{ $disbursement->amount }}" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm" placeholder="0" required>
            </div>

            <!-- Biaya Admin -->
             <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Admin (Rp)</label>
                <input type="number" name="admin_fee" value="{{ $disbursement->admin_fee }}" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm">
            </div>

            <!-- Tipe Pengeluaran -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pengeluaran</label>
                <div class="flex gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="project" {{ $disbursement->type === 'project' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700">Penyaluran Proyek (Potong Dana Proyek)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="amil" {{ $disbursement->type === 'amil' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700">Operasional Amil (Gaji / Potong Hak Amil)</span>
                    </label>
                </div>
            </div>

            <div id="programPilarSection" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                <!-- Filter Program -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select id="programFilter" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm">
                        <option value="">-- Semua Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ optional($disbursement->project)->program_id == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Pilar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilar</label>
                    <select id="pilarFilter" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm">
                        <option value="">-- Semua Pilar --</option>
                        @foreach($pillars as $pilar)
                            <option value="{{ $pilar }}" {{ optional($disbursement->project)->pilar == $pilar ? 'selected' : '' }}>{{ $pilar }}</option>
                        @endforeach
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Program / Project -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Proyek Sumber Dana (Wajib)</label>
                <select name="project_id" id="projectSelect" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm" required>
                    <option value="">-- Pilih Proyek --</option>
                    @foreach($projects as $project)
                         <option value="{{ $project->id }}" 
                                 data-program="{{ $project->program_id }}" 
                                 data-pilar="{{ $project->pilar }}"
                                 data-name="{{ trim(strtoupper($project->name)) }}"
                                 {{ $disbursement->project_id == $project->id ? 'selected' : '' }}>
                            {{ $project->program->name ?? 'No Program' }} - {{ $project->name }} (Saldo: Rp {{ number_format($project->balance, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1" id="projectCount">Menampilkan semua proyek.</p>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const programFilter = document.getElementById('programFilter');
                    const pilarFilter = document.getElementById('pilarFilter');
                    const typeRadios = document.querySelectorAll('input[name="type"]');
                    const filterSection = document.getElementById('programPilarSection');
                    const projectSelect = document.getElementById('projectSelect');
                    const projectOptions = Array.from(projectSelect.options).slice(1);
                    const projectCount = document.getElementById('projectCount');

                    function filterProjects() {
                        const selectedType = document.querySelector('input[name="type"]:checked').value;
                        const selectedProgram = programFilter.value;
                        const selectedPilar = pilarFilter.value;
                        let visibleCount = 0;

                        if (selectedType === 'amil') {
                            filterSection.style.display = 'none';
                        } else {
                            filterSection.style.display = '';
                        }

                        const currentVal = projectSelect.value;
                        let currentStillVisible = false;

                        projectOptions.forEach(option => {
                            const projectName = option.getAttribute('data-name').trim().toUpperCase();
                            const isSpaiProject = projectName === 'SPAI';
                            
                            let isVisible = false;
                            
                            if (selectedType === 'amil') {
                                isVisible = isSpaiProject;
                            } else {
                                const programMatch = !selectedProgram || option.getAttribute('data-program') === selectedProgram;
                                const pilarMatch = !selectedPilar || option.getAttribute('data-pilar') === selectedPilar;
                                isVisible = !isSpaiProject && programMatch && pilarMatch;
                            }

                            if (isVisible) {
                                option.style.display = '';
                                visibleCount++;
                                if (option.value === currentVal) currentStillVisible = true;
                                
                                if (selectedType === 'amil' && isSpaiProject && projectSelect.value === "") {
                                    projectSelect.value = option.value;
                                    currentStillVisible = true;
                                }
                            } else {
                                option.style.display = 'none';
                            }
                        });

                        // Only reset if current project is NOT the one already saved
                        if (!currentStillVisible && projectSelect.value !== "{{ $disbursement->project_id }}") {
                            // projectSelect.value = "";
                        }

                        projectCount.textContent = selectedType === 'amil' 
                            ? "Terkunci pada Proyek SPAI (Hak Amil)." 
                            : `Menampilkan ${visibleCount} proyek sesuai filter.`;
                    }

                    programFilter.addEventListener('change', filterProjects);
                    pilarFilter.addEventListener('change', filterProjects);
                    typeRadios.forEach(radio => radio.addEventListener('change', filterProjects));

                    // Custom Bank Logic
                    const bankSelect = document.getElementById('bank_sender');
                    const bankCustomGroup = document.getElementById('bank_sender_custom_group');
                    
                    bankSelect.addEventListener('change', function() {
                        if (this.value === 'Lainnya') {
                            bankCustomGroup.style.display = 'block';
                        } else {
                            bankCustomGroup.style.display = 'none';
                        }
                    });

                    // Jalankan filter saat pertama kali load
                    filterProjects();
                });
            </script>

            <!-- Keterangan -->
             <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Keperluan</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 shadow-sm">{{ $disbursement->description }}</textarea>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('finance.disbursements.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-lg">Update Disbursement</button>
        </div>
    </form>
</div>
@endsection
