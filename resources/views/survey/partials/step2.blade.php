<div id="step2-root">
<div class="w-full max-w-[960px] mx-auto px-3 sm:px-4">

  {{-- Progress Text: "6 dari 7 Pertanyaan" --}}
  <div class="mb-4 sm:mb-6">
    <div class="flex justify-between items-center mb-1">
      <span class="text-xs font-medium text-gray-600">Progress Survei</span>
      <span class="text-xs font-bold text-[#1E3A8A]">{{ $index }} dari {{ $totalQuestions }} Pertanyaan</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2">
      <div class="bg-gradient-to-r from-cyan-500 to-blue-600 h-2 rounded-full progress-bar-dynamic"
     data-progress="{{ max(0,min(100, ($totalQuestions>0 ? ($index / $totalQuestions)*100 : 0))) }}"></div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-[340px_1fr] lg:grid-cols-[360px_520px] gap-4 sm:gap-6 md:gap-x-8 md:gap-y-8 items-start justify-items-center">

    @php
        $scoreLabels = !empty($skalaLabels) && is_array($skalaLabels) ? $skalaLabels : [
            5 => 'Sangat Baik', 4 => 'Baik', 3 => 'Cukup', 2 => 'Buruk', 1 => 'Sangat Buruk'
        ];
        $gambarSrc = $kuesioner->gambar ? asset('storage/' . $kuesioner->gambar) : asset('assets/ilustrasi_default.jpg');
        $tipe = $kuesioner->tipe ?? 'radio';
        $wajib = (bool)($kuesioner->is_mandatory ?? false);
        $prefSkor = (int)($jawabanSebelumnya['skor'] ?? 0);
        $prefTeks = $jawabanSebelumnya['isi_teks'] ?? '';
    @endphp

    <div class="w-full md:flex md:justify-center">
      <div class="w-full max-w-[280px] sm:max-w-[300px] lg:max-w-[360px] mx-auto aspect-[3/2] bg-gray-100 rounded-lg overflow-hidden">
        <img src="{{ $gambarSrc }}" 
             alt="Ilustrasi" 
             width="360" 
             height="240" 
             class="w-full h-full object-cover"
             onerror="this.src='{{ asset('assets/ilustrasi_default.jpg') }}'">
      </div>
    </div>

    <div class="w-full md:max-w-[520px]">
      {{-- LOADING OVERLAY --}}
      <div id="loading-overlay" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 shadow-2xl flex flex-col items-center gap-3">
          <svg class="animate-spin h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p id="loading-text" class="text-gray-700 font-medium">Memuat pertanyaan berikutnya...</p>
        </div>
      </div>

      <form id="question-form" action="{{ route('survey.answer', ['uid' => $aplikasi->id_encrypt]) }}" method="POST">
        @csrf
        <input type="hidden" name="kuesioner_id" value="{{ $kuesioner->id }}">

        <div class="slide-in">
          <div class="mb-4 sm:mb-6">
            <p class="font-medium text-gray-800 text-sm lg:text-base leading-relaxed break-words">
              {{ str_replace(['{aplikasi}', '{$nama_aplikasi}'], [$aplikasi->nama_aplikasi, $aplikasi->nama_aplikasi], $kuesioner->pertanyaan) }}
              @if($wajib)<span class="text-red-500 ml-1">*</span>@endif
            </p>
          </div>
          
          <div class="mb-6 sm:mb-8" data-autosave-url="{{ route('survey.save.current', ['uid' => $aplikasi->id_encrypt]) }}">
            @if($tipe === 'radio')
            @php 
                $opsi = ($kuesioner->persepsi === 'risiko') 
                    ? [1, 2, 3, 4, 5]
                    : [5, 4, 3, 2, 1];
            @endphp

            <div class="grid grid-cols-1 gap-2.5 sm:gap-3">
              @foreach($opsi as $nilai)
                  <label class="cursor-pointer group">
                      <input type="radio" name="jawaban" value="{{ $nilai }}"
                            class="sr-only peer radio-input" data-auto-next="true"
                            @if($wajib) required @endif 
                            @checked((int)$prefSkor === (int)$nilai)>
                      <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-900 peer-checked:ring-2 peer-checked:ring-blue-200
                                  border-2 border-gray-200 rounded-xl px-3 sm:px-4 py-3 sm:py-3.5 text-xs sm:text-sm font-medium text-gray-700 
                                  transition-all duration-150 hover:bg-gray-50 hover:border-gray-300
                                  bg-white shadow-sm group-hover:shadow-md flex items-center justify-between">
                          <span class="break-words flex-1">{{ $scoreLabels[$nilai] ?? 'Tidak diketahui' }}</span>
                          <span class="opacity-0 peer-checked:opacity-100 transition-opacity duration-150 flex-shrink-0 ml-2">
                              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                              </svg>
                          </span>
                      </div>
                  </label>
              @endforeach
            </div>
            @else
              <div class="relative">
                <textarea name="jawaban" rows="4" placeholder="Tulis jawaban Anda di sini..."
                  class="w-full border-2 border-gray-200 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                         text-xs sm:text-sm resize-none transition-all duration-200 bg-white shadow-sm textarea-input hover:border-gray-300"
                  @if($wajib) required @endif>{{ old('jawaban', $prefTeks) }}</textarea>
                <div class="absolute bottom-2 sm:bottom-3 right-2 sm:right-3 text-gray-400">
                  <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                  </svg>
                </div>
              </div>
            @endif

            @error('jawaban')<div class="text-red-600 text-sm mt-2">{{ $message }}</div>@enderror
          </div>
          
          {{-- ✅ CLOUDFLARE TURNSTILE - HANYA DI PERTANYAAN TERAKHIR --}}
          @if($index == $totalQuestions)
          <div class="mb-4 sm:mb-6">
            <label class="block text-xs font-semibold text-gray-700 mb-2">Verifikasi Keamanan</label>
            <div class="rounded-lg p-3 border border-gray-200 bg-gray-50 flex justify-center">
              <div id="turnstile-container" data-sitekey="{{ config('turnstile.site_key') }}"></div>
            </div>
            @error('cf-turnstile-response')
            <p class="text-red-500 text-xs mt-2 flex items-center">
              <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              <span class="break-words">{{ $message }}</span>
            </p>
            @enderror
          </div>

          <script src="{{ asset('js/survey-turnstile.js') }}"></script>
          @endif

          <div class="flex flex-col-reverse sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-0">
            @if($index > 1)
              <button type="button" data-prev-url="{{ route('survey.prev', ['uid' => $aplikasi->id_encrypt]) }}" id="prev-button"
                      class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 sm:px-6 py-2.5 rounded-full shadow-sm text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-md">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Sebelumnya
              </button>
            @else
              <div class="hidden sm:block"></div>
            @endif
            
            @if($tipe === 'free_text' || $index >= $totalQuestions)
              <button type="submit"
                      class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium px-6 sm:px-8 py-2.5 rounded-full shadow-md text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg hover:scale-105 w-full sm:w-auto">
                <span class="btn-text">@if($index < $totalQuestions) Selanjutnya @else Selesai @endif</span>
                <span class="btn-icon">
                  @if($index < $totalQuestions)
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  @else
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  @endif
                </span>
              </button>
            @else
              <button type="submit" id="hidden-submit" class="hidden-submit"></button>
            @endif
          </div>
        </div>
      </form>
    </div>

  </div>
</div>
</div>