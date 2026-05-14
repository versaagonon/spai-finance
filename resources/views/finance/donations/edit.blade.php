@extends('layouts.finance')

@section('title', 'Edit Donation Entry')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Penerimaan Donasi</h2>
    
    <form action="{{ route('finance.donations.update', $donation) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $donation->date->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
            </div>

            <!-- Nama Donatur -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Donatur</label>
                <input type="text" name="donor_name" value="{{ $donation->donor_name }}" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Hamba Allah" required>
            </div>

            <!-- Bank Penerima -->
            @php
                $standardBanks = ['BSI', 'BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB', 'Permata', 'Muamalat'];
                $isCustomBank = !in_array($donation->bank_receiver, $standardBanks);
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Penerima</label>
                <select name="bank_receiver" id="bank_receiver" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                    @foreach($standardBanks as $bank)
                        <option value="{{ $bank }}" {{ $donation->bank_receiver === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                    @endforeach
                    <option value="Lainnya" {{ $isCustomBank ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Bank Penerima Lainnya -->
            <div id="bank_receiver_custom_group" style="display: {{ $isCustomBank ? 'block' : 'none' }};">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank Lainnya</label>
                <input type="text" name="bank_receiver_custom" value="{{ $isCustomBank ? $donation->bank_receiver : '' }}" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="Masukkan nama bank">
            </div>

            <!-- Uang Masuk -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Donasi (Rp)</label>
                <input type="number" name="amount" value="{{ $donation->amount }}" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm" placeholder="0" required>
            </div>

            <!-- Persentase Hak Amil -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hak Amil (%)</label>
                <select name="amil_percentage" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                    @php
                        $options = [0, 5, 12.5, 15];
                        if (!in_array($globalAmil, $options)) {
                            $options[] = $globalAmil;
                        }
                        if (!in_array($donation->amil_percentage, $options)) {
                            $options[] = $donation->amil_percentage;
                        }
                        sort($options);
                    @endphp
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" {{ $donation->amil_percentage == $opt ? 'selected' : '' }}>{{ $opt }}%</option>
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
                            <option value="{{ $program->id }}" {{ optional($donation->project)->program_id == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Pilar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilar</label>
                    <select id="pilarFilter" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">
                        <option value="">-- Semua Pilar --</option>
                        @foreach($pillars as $pilar)
                            <option value="{{ $pilar }}" {{ optional($donation->project)->pilar == $pilar ? 'selected' : '' }}>{{ $pilar }}</option>
                        @endforeach
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
                        <option value="{{ $project->id }}" 
                                data-program="{{ $project->program_id }}" 
                                data-pilar="{{ $project->pilar }}" 
                                data-amil="{{ $projectAmil }}"
                                {{ $donation->project_id == $project->id ? 'selected' : '' }}>
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

                        // Only reset if current project is NOT the one already saved
                        if (!currentStillVisible && projectSelect.value !== "{{ $donation->project_id }}") {
                            // projectSelect.value = "";
                        }

                        projectCount.textContent = `Menampilkan ${visibleCount} proyek sesuai filter.`;
                    }

                    function updateAmil() {
                        const selectedOption = projectSelect.options[projectSelect.selectedIndex];
                        if (selectedOption && selectedOption.value !== "") {
                            const amilValue = selectedOption.getAttribute('data-amil');
                            
                            let optionExists = false;
                            for (let i = 0; i < amilInput.options.length; i++) {
                                if (parseFloat(amilInput.options[i].value) === parseFloat(amilValue)) {
                                    amilInput.value = amilInput.options[i].value;
                                    optionExists = true;
                                    break;
                                }
                            }

                            if (!optionExists) {
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

                    // Custom Bank Logic
                    const bankSelect = document.getElementById('bank_receiver');
                    const bankCustomGroup = document.getElementById('bank_receiver_custom_group');
                    
                    bankSelect.addEventListener('change', function() {
                        if (this.value === 'Lainnya') {
                            bankCustomGroup.style.display = 'block';
                        } else {
                            bankCustomGroup.style.display = 'none';
                        }
                    });

                    // Initial filter
                    filterProjects();
                });
            </script>

            <!-- Notes -->
             <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 shadow-sm">{{ $donation->notes }}</textarea>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('finance.donations.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-center">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg">Update Transaction</button>
        </div>
    </form>
</div>
@endsection
