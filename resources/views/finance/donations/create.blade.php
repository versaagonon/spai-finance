@extends('layouts.finance')

@section('title', 'New Donation Entry')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Input Penerimaan Donasi</h2>
    
    <form action="{{ route('finance.donations.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="date" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
            </div>

            <!-- Nama Donatur -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Donatur</label>
                <input type="text" name="donor_name" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Hamba Allah" required>
            </div>

            <!-- Bank Penerima -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Penerima</label>
                <select name="bank_receiver" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                    <option value="BSI">BSI</option>
                    <option value="BCA">BCA</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="BNI">BNI</option>
                    <option value="BRI">BRI</option>
                    <option value="CIMB">CIMB</option>
                    <option value="Permata">Permata</option>
                    <option value="Muamalat">Muamalat</option>
                </select>
            </div>

            <!-- Uang Masuk -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Donasi (Rp)</label>
                <input type="number" name="amount" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="0" required>
            </div>

            <!-- Persentase Hak Amil -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hak Amil (%)</label>
                <select name="amil_percentage" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                    @php
                        $options = [0, 5, 12.5, 15];
                        if (!in_array($globalAmil, $options)) {
                            $options[] = $globalAmil;
                            sort($options);
                        }
                    @endphp
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" {{ $globalAmil == $opt ? 'selected' : '' }}>{{ $opt }}% {{ $globalAmil == $opt ? '(Global)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                <!-- Filter Program -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select id="programFilter" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                        <option value="">-- Semua Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Pilar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilar</label>
                    <select id="pilarFilter" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                        <option value="">-- Semua Pilar --</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Kemanusiaan">Kemanusiaan</option>
                        <option value="Dakwah">Dakwah</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Operasional">Operasional</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Program / Project -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Proyek (Wajib)</label>
                <select name="project_id" id="projectSelect" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
                    <option value="">-- Pilih Proyek --</option>
                    @foreach($projects as $project)
                        @php
                            $projectAmil = $globalAmil;
                            if ($project->use_custom_amil) {
                                $projectAmil = $project->amil_percentage;
                            } elseif ($project->program && $project->program->amil_percentage > 0) {
                                $projectAmil = $project->program->amil_percentage;
                            }
                        @endphp
                        <option value="{{ $project->id }}" data-program="{{ $project->program_id }}" data-pilar="{{ $project->pilar }}" data-amil="{{ $projectAmil }}">
                            {{ $project->program->name ?? 'No Program' }} - {{ $project->name }} ({{ $project->pilar ?? 'General' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1" id="projectCount">Menampilkan semua proyek.</p>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const programFilter = document.getElementById('programFilter');
                    const pilarFilter = document.getElementById('pilarFilter');
                    const projectSelect = document.getElementById('projectSelect');
                    const amilInput = document.querySelector('select[name="amil_percentage"]');
                    const projectOptions = Array.from(projectSelect.options).slice(1);
                    const projectCount = document.getElementById('projectCount');

                    function filterProjects() {
                        const selectedProgram = programFilter.value;
                        const selectedPilar = pilarFilter.value;
                        let visibleCount = 0;

                        const currentVal = projectSelect.value;
                        let currentStillVisible = false;

                        projectOptions.forEach(option => {
                            const programMatch = !selectedProgram || option.getAttribute('data-program') === selectedProgram;
                            const pilarMatch = !selectedPilar || option.getAttribute('data-pilar') === selectedPilar;

                            if (programMatch && pilarMatch) {
                                option.style.display = '';
                                visibleCount++;
                                if (option.value === currentVal) currentStillVisible = true;
                            } else {
                                option.style.display = 'none';
                            }
                        });

                        if (!currentStillVisible && projectSelect.value !== "") {
                            projectSelect.value = "";
                        }

                        projectCount.textContent = `Menampilkan ${visibleCount} proyek sesuai filter.`;
                    }

                    function updateAmil() {
                        const selectedOption = projectSelect.options[projectSelect.selectedIndex];
                        if (selectedOption && selectedOption.value !== "") {
                            const amilValue = selectedOption.getAttribute('data-amil');
                            
                            // Debugging
                            console.log("Selected project amil:", amilValue);

                            let optionExists = false;
                            for (let i = 0; i < amilInput.options.length; i++) {
                                // Gunakan parseFloat untuk perbandingan angka yang akurat
                                if (parseFloat(amilInput.options[i].value) === parseFloat(amilValue)) {
                                    amilInput.value = amilInput.options[i].value;
                                    optionExists = true;
                                    break;
                                }
                            }

                            if (!optionExists) {
                                // Jika nilai tidak ada di list, buat opsi baru
                                const newOpt = document.createElement('option');
                                newOpt.value = amilValue;
                                newOpt.text = amilValue + "% (Sesuai Pengaturan)";
                                amilInput.add(newOpt);
                                amilInput.value = amilValue;
                            }
                        }
                    }

                    programFilter.addEventListener('change', filterProjects);
                    pilarFilter.addEventListener('change', filterProjects);
                    projectSelect.addEventListener('change', updateAmil);
                });
            </script>

            <!-- Notes -->
             <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm"></textarea>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">
            <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg">Save Transaction</button>
        </div>
    </form>
</div>
@endsection
