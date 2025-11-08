<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div id="overlay" class="overlay"></div>

    <div class="flex h-screen overflow-hidden">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')

            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6">
                <div class="max-w-2xl mx-auto">

                    @if(session('must_change_password'))
                    <div class="alert alert-danger alert-dismissible fade show border-start border-danger border-5" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-2">
                                    <i class="fas fa-shield-alt me-1"></i>Ganti Password Wajib!
                                </h6>
                                <p class="mb-0">
                                    Password Anda sudah lebih dari 3 bulan dan <strong>harus diganti</strong> untuk keamanan akun. 
                                    Anda <strong class="text-decoration-underline">tidak dapat mengakses sistem</strong> sampai password diganti.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="alert alert-warning bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-yellow-800">{{ session('warning') }}</span>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-key mr-2"></i>
                                {{ session('must_change_password') ? 'Ganti Password (Wajib)' : 'Ubah Password' }}
                            </h2>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}" class="p-6">
                            @csrf

                            <div class="mb-4">
                                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-lock text-blue-600 mr-1"></i>
                                    Password Saat Ini
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password"
                                       class="form-control w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('current_password') border-red-500 @enderror" 
                                       required
                                       autocomplete="current-password">
                                @error('current_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-key text-blue-600 mr-1"></i>
                                    Password Baru
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       name="new_password" 
                                       id="new_password"
                                       class="form-control w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('new_password') border-red-500 @enderror" 
                                       required
                                       autocomplete="new-password">
                                <small class="text-gray-600 text-xs mt-1 block">
                                    Minimal 8 karakter, kombinasi huruf besar&kecil, angka, dan simbol
                                </small>
                                @error('new_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-check-circle text-blue-600 mr-1"></i>
                                    Konfirmasi Password Baru
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       name="new_password_confirmation" 
                                       id="new_password_confirmation"
                                       class="form-control w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       required
                                       autocomplete="new-password">
                            </div>

                            <div class="flex items-center justify-end space-x-3">
                                @if(!session('must_change_password'))
                                <a href="{{ route('dashboard') }}" 
                                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Batal
                                </a>
                                @endif

                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center font-semibold shadow-md hover:shadow-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ session('must_change_password') ? 'Ganti Password & Lanjutkan' : 'Simpan Perubahan' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    @if(!session('must_change_password'))
                    <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tips Password Aman:
                        </h4>
                        <ul class="text-xs text-blue-700 space-y-1 ml-5 list-disc">
                            <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
                            <li>Minimal 8 karakter (lebih panjang lebih baik)</li>
                            <li>Jangan gunakan informasi pribadi yang mudah ditebak</li>
                            <li>Ganti password secara berkala (setiap 3 bulan)</li>
                        </ul>
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <div id="toast-sukses" class="fixed top-5 right-5 z-[9999] hidden">
        <div class="rounded-xl px-4 py-3 shadow-xl text-white min-w-[240px] toast-glass-effect">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 icon-yellow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                </svg>
                <div>
                    <p class="font-semibold">Berhasil!</p>
                    <p id="toast-sukses-msg" class="text-sm/5 opacity-90"></p>
                </div>
            </div>
            <div id="toast-sukses-bar" class="h-1 mt-3 bg-white/30 rounded overflow-hidden">
                <div class="h-full w-full transform-origin-left"></div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/auth.js') }}" defer></script>

    @if (session('success'))
    <script nonce="{{ request()->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            const msg = document.getElementById('toast-sukses-msg');
            if (msg) {
                msg.textContent = @json(session('success'));
            }
        });
    </script>
    @endif

</body>
</html>