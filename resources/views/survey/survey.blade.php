<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-RISMA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Permissions-Policy" content="private-token=()">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- ✅ EXTERNAL CSS --}}
    <link rel="stylesheet" href="{{ asset('css/survey.css') }}">
</head>
<body class="min-h-screen wave-background">
    <div class="bg-circle c1"></div>
    <div class="bg-circle c2"></div>
    <div class="dot d1"></div>
    <div class="dot d2"></div>
    <div class="dot d3"></div>
    
    <div class="min-h-screen flex flex-col">
        <div class="flex-shrink-0 relative">
            <div class="bg-white/20 relative rounded-b-[60px] sm:rounded-b-[80px] md:rounded-b-[120px] pb-4 sm:pb-6 pt-3 sm:pt-4 border border-white/30">
                <div class="container mx-auto px-3 sm:px-4 text-center relative">
                    <div class="mb-2 sm:mb-3">                     
                        <img
                            src="{{ asset('assets/logo_baru.png') }}"
                            class="mx-auto w-32 sm:w-40 md:w-56 lg:w-64 h-auto"
                            loading="eager"
                        />
                        <h2 class="text-white text-base sm:text-lg md:text-xl font-bold mt-1 sm:mt-2 px-2">
                            @if(isset($aplikasi) && $aplikasi)
                                Aplikasi <span class="text-[#FACC15]">{{ $aplikasi->nama_aplikasi }}</span>
                            @else
                                Aplikasi Pemerintah
                            @endif
                        </h2>
                        <div class="w-12 sm:w-16 h-1 sm:h-1.5 bg-[#FACC15] mx-auto mt-2 rounded-full"></div>
                    </div>
                    
                    <p class="text-xs md:text-sm text-white/90 mt-2 sm:mt-3 leading-relaxed px-2">
                        Bantu kami tingkatkan layanan digital melalui survei singkat ini
                    </p>
                </div>
            </div>
        </div>

        <div class="flex-1 px-3 sm:px-4 md:px-6 py-3 sm:py-4 pb-6 sm:pb-8 overflow-y-auto overflow-x-hidden">
            <div class="min-h-full flex items-start justify-center">
                <div class="w-full max-w-5xl">
                    <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl overflow-hidden">
                        <div class="survey-content bg-white rounded-xl sm:rounded-2xl shadow-xl p-3 sm:p-4 md:p-8 lg:p-12">
                            @if ($step == 1)
                                @include('survey.partials.step1')
                            @elseif ($step == 2)
                                <div id="question-container">
                                    @include('survey.partials.step2', [
                                        'aplikasi' => $aplikasi,
                                        'kuesioner' => $kuesioner,
                                        'index' => $index,
                                        'totalQuestions' => $totalQuestions,
                                        'jawabanSebelumnya' => $jawabanSebelumnya ?? null,
                                        'skalaLabels' => $skalaLabels ?? [],
                                    ])
                                </div>
                            @elseif ($step == 3)
                                @include('survey.partials.selesai')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/survey.js') }}"></script>
    
    @if ($step == 2)
        <script src="{{ asset('js/survey-step2.js') }}"></script>
    @endif

</body>
</html>