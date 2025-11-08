<header class="bg-white relative">
  <div class="relative flex items-center justify-between h-20 px-6">

    <div class="flex items-center space-x-4">
      <button id="sidebarToggle"
              class="md:hidden p-2 rounded-xl text-gray-600 hover:text-[#1E3A8A] hover:bg-gray-100 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <div class="flex items-center space-x-4">
        <div class="hidden md:flex items-center justify-center w-10 h-10 rounded-xl bg-[#1E3A8A] shadow">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Z"/>
          </svg>
        </div>
        <div class="flex flex-col">
          <div class="flex items-center space-x-2">
            <h1 class="text-xl md:text-2xl font-bold text-[#1E3A8A]">
              <span id="tanggal-hari" class="text-sm md:text-base font-bold"></span>
            </h1>
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center space-x-3">
      <div class="relative">
        <button id="userMenuButton"
                class="group flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-[#1E3A8A]/20 hover:border-[#1E3A8A]/40 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 shadow-sm">
          <div class="flex items-center space-x-2">
            <div class="relative">
              <div class="w-9 h-9 rounded-lg bg-[#1E3A8A] flex items-center justify-center shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
            </div>

            <div class="hidden md:flex flex-col text-left leading-tight">
              <span class="text-base font-semibold text-[#1E3A8A]">
                @if(session('user'))
                  {{ session('user')->username ?? 'admin' }}
                @else
                  admin
                @endif
              </span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#1E3A8A]" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </div>
        </button>

        <div id="userMenu"
              class="hidden absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border border-[#1E3A8A]/15 py-2 z-50 transition-all duration-200 opacity-0 scale-95">
          
          @if(session('password_warning'))
            <div class="px-3 py-2 border-b border-gray-100">
              <div class="flex items-start space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                  <p class="text-xs text-amber-600 font-medium">Perhatian!</p>
                  <p class="text-xs text-gray-600">Password perlu diubah</p>
                </div>
              </div>
            </div>
          @endif

          <div class="py-1">
            <a href="{{ route('password.change') }}"
              class="group flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#1E3A8A] transition-colors duration-150">
              <svg xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  class="h-5 w-5 mr-3 text-blue-500 group-hover:text-[#1E3A8A]"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="5" y="11" width="14" height="9" rx="2" ry="2"></rect>
                <path d="M8 11V8a4 4 0 0 1 8 0v3"></path>
              </svg>
              Ubah Password
            </a>
            <hr class="my-1 border-gray-100">
            
            <form action="{{ route('logout') }}" method="POST" class="display-inline">
              @csrf
              <button type="submit"
                      class="group w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-red-500 group-hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="absolute bottom-0 left-0 right-0 bottom-emboss"></div>
</header>