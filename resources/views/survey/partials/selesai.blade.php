<!-- WRAPPER terpusat; sama seperti step1/step2 -->
<div class="w-full max-w-[960px] mx-auto px-3 sm:px-4">

  <!-- GRID: kiri gambar, kanan konten (ukuran kolom identik) -->
  <div class="grid grid-cols-1 md:grid-cols-[340px_1fr] lg:grid-cols-[360px_520px] gap-4 sm:gap-6 md:gap-x-8 md:gap-y-8 items-start justify-items-center">

    <!-- ILUSTRASI (ukuran disamakan dengan step1/step2) -->
    <div class="w-full md:flex md:justify-center">
      <img
        src="{{ asset('assets/selesai.jpg') }}"
        alt="Terima kasih"
        loading="lazy"
        class="w-[280px] sm:w-[300px] lg:w-[360px] max-w-full h-auto mx-auto transition duration-500 ease-in-out rounded-lg"
      >
    </div>

    <!-- PANEL KANAN (lebar identik; konten ditengah kolom) -->
    <div class="w-full md:max-w-[520px]">
      <!-- tanpa card tambahan; hanya blok konten yang rata-tengah -->
      <div class="slide-in min-h-[240px] sm:min-h-[280px] flex items-center justify-center text-center px-2">
        <div class="space-y-2.5 sm:space-y-3 md:space-y-4">
          <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#1E3A8A]">
            Terima kasih! 🎉
          </h2>

          <p class="text-gray-700 text-xs sm:text-sm lg:text-base leading-relaxed">
            Tanggapan Anda telah <span class="font-semibold text-[#1E3A8A]">berhasil terekam</span>.
            Masukan ini membantu kami meningkatkan kualitas layanan
            <span class="font-semibold">
              {{ optional($aplikasi)->nama_aplikasi ?? 'aplikasi terkait' }}
            </span>
            @if(optional($aplikasi?->opd)->nama_opd)
              pada <span class="font-semibold">{{ $aplikasi->opd->nama_opd }}</span>
            @endif
            . Terima kasih sudah meluangkan waktu. 🙏
          </p>
        </div>
      </div>
    </div>

  </div>
</div>