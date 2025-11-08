<!-- WRAPPER terpusat; jarak kiri-kanan simetris -->
<div class="w-full max-w-[960px] mx-auto px-3 sm:px-4">

  <!-- GRID dua kolom presisi: kiri gambar, kanan form -->
  <div class="grid grid-cols-1 md:grid-cols-[340px_1fr] lg:grid-cols-[360px_520px] gap-4 sm:gap-6 md:gap-x-8 md:gap-y-8 items-start justify-items-center">

    <!-- ILUSTRASI (konsisten dengan step2) -->
    <div class="w-full md:flex md:justify-center">
      <img
        src="{{ asset('assets/halaman 1.jpg') }}"
        alt="Ilustrasi Survey"
        class="w-[280px] sm:w-[300px] lg:w-[360px] max-w-full h-auto mx-auto transition duration-500 ease-in-out"
      >
    </div>

    <!-- FORM (kolom kanan dibatasi; tidak mentok kanan) -->
    <div class="w-full md:max-w-[520px]">
      <form action="{{ route('survey.store.step1', ['uid' => $aplikasi->id_encrypt]) }}" method="POST" class="w-full">
        @csrf
        {{-- penting: sesuai validateStep1 -> exists:aplikasi,id_encrypt --}}
        <input type="hidden" name="aplikasi_id" value="{{ $aplikasi->id_encrypt }}">

        <div class="slide-in">
          <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#1E3A8A] mb-4 sm:mb-6">
            Form Profil Responden
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">

            <!-- NAMA -->
            <div>
              <label for="nama" class="block font-medium text-gray-700 mb-1">
                Nama <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3 flex items-center pointer-events-none">
                  <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                  </svg>
                </span>
                <input
                  type="text"
                  id="nama"
                  name="nama"
                  required
                  pattern="[A-Za-z\s'\.\-]+"
                  title="Nama hanya boleh berisi huruf, spasi, titik, tanda petik, dan tanda hubung"
                  placeholder="Masukkan nama"
                  class="w-full pl-8 sm:pl-10 pr-2.5 sm:pr-3 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm transition-all duration-200"
                  value="{{ $respondenData['nama'] ?? old('nama') }}"
                >
              </div>
              @error('nama')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <!-- USIA -->
            <div>
              <label for="usia" class="block font-medium text-gray-700 mb-1">
                Usia <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3 flex items-center pointer-events-none">
                  <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </span>
                <input
                  type="tel"
                  name="usia"
                  id="usia"
                  min="17" max="99"
                  maxlength="3"
                  required
                  inputmode="numeric"
                  placeholder="Masukkan usia (min 17)"
                  class="w-full pl-8 sm:pl-10 pr-2.5 sm:pr-3 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm transition-all duration-200"
                  value="{{ $respondenData['usia'] ?? old('usia') }}"
                />
              </div>
              @error('usia')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <!-- NO HP -->
            <div>
              <label for="no_hp" class="block font-medium text-gray-700 mb-1">
                No. HP <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3 flex items-center pointer-events-none">
                  <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6.62 10.79a15.054 15.054 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21 11.36 11.36 0 003.55.57 1 1 0 011 1v3.5a1 1 0 01-1 1C9.85 22 2 14.15 2 4a1 1 0 011-1H6.5a1 1 0 011 1c0 1.21.2 2.4.57 3.55a1 1 0 01-.21 1.11l-2.2 2.2z"/>
                  </svg>
                </span>
                <input
                  type="tel"
                  name="no_hp"
                  id="no_hp"
                  pattern="^08[0-9]{8,11}$"
                  maxlength="13"
                  required
                  inputmode="numeric"
                  placeholder="08xxxxxxxxxx"
                  class="w-full pl-8 sm:pl-10 pr-2.5 sm:pr-3 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm transition-all duration-200"
                  value="{{ $respondenData['no_hp'] ?? old('no_hp') }}"
                />
              </div>
              @error('no_hp')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>

            <!-- JENIS KELAMIN -->
            <div>
              <label for="jenis_kelamin" class="block font-medium text-gray-700 mb-1">
                Jenis Kelamin <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3 flex items-center pointer-events-none">
                  <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </span>
                <select
                  id="jenis_kelamin"
                  name="jenis_kelamin"
                  required
                  class="w-full pl-8 sm:pl-10 pr-8 sm:pr-10 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm appearance-none transition-all duration-200 bg-white"
                >
                  @php $jk = $respondenData['jenis_kelamin'] ?? old('jenis_kelamin'); @endphp
                  <option value="" disabled {{ $jk ? '' : 'selected' }}>Pilih jenis kelamin</option>
                  <option value="Laki-laki" {{ $jk === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                  <option value="Perempuan" {{ $jk === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 sm:pr-3 pointer-events-none">
                  <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
              @error('jenis_kelamin')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
          
          <!-- Submit Button -->
          <div class="mt-6 sm:mt-8 flex justify-end">
            <button type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium px-6 sm:px-8 py-2 sm:py-2.5 rounded-full shadow-md text-xs sm:text-sm transition-all duration-200 flex items-center gap-2 hover:shadow-lg hover:scale-105 w-full sm:w-auto justify-center">
              <span class="btn-text">Mulai Survei</span>
              <span class="btn-icon">
                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </span>
            </button>
          </div>

        </div>
      </form>
    </div>

  </div>
</div>
