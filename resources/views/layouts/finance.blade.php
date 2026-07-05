<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPAI Financial Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:block">
            <div class="p-6">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="SPAI Logo" class="h-50 w-auto rounded-lg">
                </div>
            </div>
            <nav class="mt-6">
                <a href="{{ route('finance.dashboard') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.dashboard') ? 'bg-green-50 text-green-600 border-r-4 border-green-600' : '' }}">
                    <i class="fas fa-chart-line w-6"></i> Dashboard
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('finance.donations.index') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.donations.*') ? 'bg-green-50 text-green-600 border-r-4 border-green-600' : '' }}">
                    <i class="fas fa-hand-holding-dollar w-6"></i> Donasi
                </a>
                @endif
                 @if(auth()->user()->role === 'admin')
                 <a href="{{ route('finance.disbursements.index') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.disbursements.*') ? 'bg-green-50 text-green-600 border-r-4 border-green-600' : '' }}">
                    <i class="fas fa-money-bill-wave w-6"></i> Penyaluran
                </a>
                <a href="{{ route('finance.programs.index') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.programs.*') ? 'bg-green-50 text-green-600 border-r-4 border-green-600' : '' }}">
                     <i class="fas fa-layer-group w-6"></i> Program & Proyek
                </a>
                @endif
                <div class="px-6 py-4 mt-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Laporan</p>
                    <div class="space-y-1">
                        <a href="{{ route('finance.report.receipts') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.report.receipts') ? 'bg-green-50 text-green-600 font-medium' : '' }}">
                            <i class="fas fa-file-invoice-dollar w-5"></i> Penerimaan
                        </a>
                        <a href="{{ route('finance.report.disbursements') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.report.disbursements') ? 'bg-green-50 text-green-600 font-medium' : '' }}">
                            <i class="fas fa-file-export w-5"></i> Penyaluran
                        </a>
                        <a href="{{ route('finance.report.amil') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.report.amil') ? 'bg-green-50 text-green-600 font-medium' : '' }}">
                            <i class="fas fa-user-shield w-5"></i> Hak Amil
                        </a>
                    </div>
                </div>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('finance.settings.index') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition-colors {{ request()->routeIs('finance.settings.*') ? 'bg-green-50 text-green-600 border-r-4 border-green-600' : '' }}">
                    <i class="fas fa-cog w-6"></i> Pengaturan
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Header -->
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <button class="text-gray-500 focus:outline-none md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800 ml-4">@yield('title', 'Dashboard')</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex gap-2">
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('finance.donations.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm flex items-center gap-2">
                            <i class="fas fa-plus"></i> Input Donasi
                        </a>
                        <a href="{{ route('finance.disbursements.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm flex items-center gap-2">
                            <i class="fas fa-minus"></i> Input Penyaluran
                        </a>
                        @endif
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-300 overflow-hidden shadow-sm">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff" alt="Avatar">
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="ml-2">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-red-500 transition px-2.5 py-1.5 bg-gray-100 hover:bg-red-50 rounded-lg border border-transparent hover:border-red-200" title="Keluar dari sistem">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </header>

            <main class="w-full flex-grow p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
