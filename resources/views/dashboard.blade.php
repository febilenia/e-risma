<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-50">
    <div id="overlay" class="overlay"></div>

    <div class="flex h-screen overflow-hidden">
        @include('components.sidebar')
        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')
            
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6">
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <h2 class="text-xl font-semibold text-gray-800 text-center md:text-left">
                            Selamat Datang di Dashboard {{ $opdName ? $opdName : 'SuperAdmin' }}
                        </h2>
                    </div>
                </div>

                {{-- CARD PREDIKAT NKP KESELURUHAN --}}
                <div class="mb-8">
                    <div class="predikat-card stat-card rounded-xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">
                                <i class="fas fa-award mr-2"></i>Predikat NKP(Nilai Kepuasan Pengguna) {{ $opdName ? 'Aplikasi ' . $opdName : 'Keseluruhan Aplikasi di Kabupaten Gresik' }}
                            </h3>
                            <i class="fas fa-chart-line text-2xl opacity-75"></i>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div id="predikatolahanNkp" class="predikat-value">0.00</div>
                                <div class="predikat-description">Rata-rata NKP Keseluruhan</div>
                            </div>
                            <div class="text-center">
                                <div id="predikatBadge" class="badge-predikat badge-belum-data">
                                    Memuat...
                                </div>
                                <div class="predikat-description mt-2">Predikat</div>
                            </div>
                            <div class="text-center">
                                <div id="jumlahAplikasiAnalisis" class="predikat-value">0</div>
                                <div class="predikat-description">Aplikasi Teranalisis</div>
                            </div>
                        </div>
                        
                        <div class="insight-box" id="insightBox">
                            <div class="flex items-start">
                                <i class="fas fa-lightbulb mr-3 mt-1 text-yellow-300"></i>
                                <div id="insightText" class="text-sm">
                                    Memuat insight layanan digital...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RINGKASAN ANALISIS NKP --}}
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-chart-line mr-2 text-blue-600"></i>Ringkasan Analisis NKP
                        </h3>
                        <div class="flex space-x-2">
                            <div class="input-group input-group-sm filter-date-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="text" id="filterDateRange" class="form-control date-range-input" placeholder="Pilih rentang tanggal..." readonly>
                                <button class="btn btn-outline-secondary" type="button" id="clearDateFilter" title="Hapus Filter">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <a href="{{ route('analisis.index') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Detail Analisis
                            </a>
                        </div>
                    </div>

                    <!-- Cards Analisis -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white shadow rounded-xl p-6 stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 mb-1">Total Aplikasi{{ $opdName ? ' ' . $opdName : '' }}</p>
                                    <p id="anaTotalAplikasi" class="text-2xl font-bold text-gray-800">0</p>
                                </div>
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                                    <i class="fas fa-th-large"></i>
                                </span>
                            </div>
                        </div>
                        
                        @if($isSuperadmin)
    {{-- ✅ Card NKP Tertinggi - Hanya untuk Superadmin --}}
    <div class="bg-white shadow rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="flex-1">
                <p class="text-gray-500 mb-1">NKP Tertinggi</p>
                <p id="anaNkpTertinggi" class="text-2xl font-bold text-gray-800">0.00</p>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="namaAplikasiTertinggi" class="font-semibold text-blue-700 block">-</span>
                    <span id="namaOpdTertinggi" class="text-gray-400 text-xs block">-</span>
                    Predikat: <span id="predikatTertinggi" class="font-semibold text-green-600">-</span>
                </p>
            </div>
            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-arrow-up"></i>
            </span>
        </div>
    </div>
                
    {{-- ✅ Card NKP Terendah - Hanya untuk Superadmin --}}
    <div class="bg-white shadow rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="flex-1">
                <p class="text-gray-500 mb-1">NKP Terendah</p>
                <p id="anaNkpTerendah" class="text-2xl font-bold text-gray-800">0.00</p>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="namaAplikasiTerendah" class="font-semibold text-blue-700 block">-</span>
                    <span id="namaOpdTerendah" class="text-gray-400 text-xs block">-</span>
                    Predikat: <span id="predikatTerendah" class="font-semibold text-red-600">-</span>
                </p>
            </div>
            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-arrow-down"></i>
            </span>
        </div>
    </div>
    @endif
    
    <div class="bg-white shadow rounded-xl p-6 stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 mb-1">Total Responden</p>
                <p id="anaTotalResponden" class="text-2xl font-bold text-gray-800">0</p>
            </div>
            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-users"></i>
            </span>
        </div>
    </div>
</div>
                    <!-- Chart Performa -->
                    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                        <div class="bg-white shadow rounded-xl p-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">
                                    <i class="fas fa-chart-bar mr-2 text-indigo-600"></i> Performa Aplikasi (NKP)
                                </h4>
                            </div>
                            <div class="h-64">
                                <canvas id="dashPerformaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script nonce="{{ request()->get('csp_nonce') }}">
    window.dashboardOpdName = @json($opdName ?? 'Kabupaten Gresik');
    window.isSuperadmin = @json($isSuperadmin ?? false); 
</script>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>