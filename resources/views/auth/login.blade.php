<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>E-RISMA - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>

<body class="min-h-screen wave-background flex items-center justify-center p-4">
  
  <!-- Card login -->
  <div class="glass-container rounded-3xl w-full max-w-4xl mx-auto shadow-2xl overflow-hidden animate-fade-in">
    <div class="flex flex-col lg:flex-row items-stretch">
      
      <!-- Kiri: ilustrasi -->
      <div class="relative w-full lg:w-2/5 bg-blue-900/5 backdrop-blur-sm flex items-center justify-center p-6 lg:p-8">
        <div class="relative">
          <div class="text-center p-4">
            <img src="{{ asset('assets/logo_baru.png') }}" 
                alt="Logo SIPEKA"
                class="mx-auto h-40 md:h-56 lg:h-64 object-contain" />
          </div>
        </div>
      </div>

      <!-- Kanan: form -->
      <div class="w-full lg:w-3/5 p-6 lg:p-8 flex items-center">
        <div class="w-full max-w-sm mx-auto animate-slide-up">
          
          <div class="text-center mb-6">
            
            <h1 class="text-2xl font-bold text-white mb-1">Login Form</h1>
            <p class="text-white/80 text-sm">Silakan login untuk mengakses akun Anda.</p>
          </div>

          @if ($errors->any())
            <div class="bg-red-50/80 border border-red-200 text-red-700 p-3 rounded-lg mb-4">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-xs font-medium">{{ $errors->first() }}</span>
              </div>
            </div>
          @endif

          <form method="POST" action="/login" class="space-y-4" autocomplete="off">
            @csrf

            <div>
              <label class="block text-xs font-semibold text-white/90 mb-1">Username</label>
              <input type="text" 
                     name="username" 
                     class="glass-input w-full px-3 py-2.5 rounded-lg text-sm"
                     placeholder="Username" 
                     required>
            </div>

            <div>
              <label class="block text-xs font-semibold text-white/90 mb-1">Password</label>
              <div class="relative">
                <input type="password"
                       id="password"
                       name="password"
                       class="glass-input w-full px-3 py-2.5 pr-12 rounded-lg text-sm"
                       placeholder="Password"
                       required />

                <button type="button"
                        id="togglePassword"
                        class="absolute top-1/2 -translate-y-1/2 right-0 flex items-center pr-3 text-white/70 hover:text-yellow-300 focus:outline-none"
                        aria-label="Tampilkan/sembunyikan password">
                  
                  <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>

                  <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-white/90 mb-1">Security Verification</label>
              <div class="rounded-lg p-3 mb-2 border border-white/20 bg-white/5 backdrop-blur">
                <div class="flex items-center justify-between gap-3 mb-2">
                  <span class="captcha-container bg-white rounded p-2">
                    <img src="{{ captcha_src('flat') }}" alt="captcha">
                  </span>

                  <button type="button" 
                        id="reload" 
                        class="btn-yellow inline-flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors duration-200">
                  <svg class="w-4 h-4 mr-1.5 transform hover:rotate-180 transition-transform duration-300 svg-icon-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  <span>Refresh</span>
                </button>
                </div>
                
                <input type="text" 
                       name="captcha" 
                       class="glass-input w-full px-3 py-2 rounded-md text-sm placeholder-white/70" 
                       placeholder="Masukkan captcha" 
                       required>
                
                @if ($errors->has('captcha'))
                <p class="text-red-300 text-xs mt-1 flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                  {{ $errors->first('captcha') }}
                </p>
                @endif
              </div>
            </div>

            <button type="submit" 
                class="btn-yellow w-full py-3 rounded-lg font-semibold text-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
          Login
        </button>
          </form>
          
        </div>
      </div>
    </div>
  </div>

  <div id="toast-sukses" class="hidden">
    <div class="glass-container rounded-xl px-4 py-3 shadow-xl border border-white/30 backdrop-blur text-white min-w-[240px]">
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
        
        if (window.initToastNotification) {
          window.initToastNotification();
        }
      }
    });
  </script>
  @endif

</body>
</html>