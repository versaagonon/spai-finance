<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode PIN | SPAI Finance Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-40 -right-40 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-40 left-20 w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="w-full max-w-sm p-8">
        <div class="glass-panel shadow-2xl rounded-3xl p-10 border border-white text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-6 shadow-inner shadow-green-200">
                <i class="fas fa-lock text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight mb-2">Verifikasi Keamanan</h2>
            <p class="text-gray-500 text-sm font-medium mb-8">Masukkan 6-digit PIN administrator Anda.</p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl mb-6 flex items-center justify-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle pt-0.5"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            <form action="{{ route('login.pin.post') }}" method="POST" id="pinForm">
                @csrf
                <div class="flex justify-center gap-1.5 mb-8" dir="ltr">
                    @for($i = 0; $i < 6; $i++)
                        <input type="password" name="pin[]" maxlength="1" class="pin-input w-10 h-12 text-center text-xl font-bold rounded-xl border border-gray-300 focus:border-green-500 focus:ring-4 focus:ring-green-500/20 transition-all bg-gray-50 focus:bg-white text-gray-800 outline-none shadow-sm" pattern="[0-9]*" inputmode="numeric" autocomplete="off" {{ $i === 0 ? 'autofocus' : '' }}>
                    @endfor
                </div>

                <!-- Fallback button, usually hidden by JS -->
                <button type="submit" id="submitBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-green-600/30 transition-all transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                    <span>Verifikasi</span>
                    <i class="fas fa-check"></i>
                </button>
            </form>
            
            <div class="mt-6">
                 <form action="{{ route('logout') }}" method="POST">
                    @csrf
                     <button type="submit" class="text-sm text-gray-400 hover:text-red-500 transition-colors">Batal Login</button>
                 </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.pin-input');
            const form = document.getElementById('pinForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Hide submit button to rely on auto-submit, show fallback just in case
            submitBtn.style.display = 'none';

            inputs.forEach((input, index) => {
                // Focus on load for first input
                if(index === 0) input.focus();

                input.addEventListener('input', function(e) {
                    // Hanya izinkan angka
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        } else {
                            // Cek jika semua terisi, otomatis submit
                            let allFilled = true;
                            inputs.forEach(inp => {
                                if(inp.value.length === 0) allFilled = false;
                            });
                            
                            if (allFilled) {
                                // Mencegah double click/submit
                                inputs.forEach(inp => inp.blur());
                                setTimeout(() => form.submit(), 150);
                            }
                        }
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace') {
                        if (!this.value && index > 0) {
                            e.preventDefault(); // Hindari back navigasi browser
                            inputs[index - 1].focus();
                            inputs[index - 1].value = ''; // Hapus value input sebelumnya
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
