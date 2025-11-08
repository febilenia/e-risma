<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Ditutup</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/survey-closed.css') }}">
</head>

<body>
    <div class="closed-wrap">
        <div class="card-closed">
            <div class="icon-container">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h2 class="title">Survey Ditutup</h2>
            <div class="amber-bar"></div>
            <p class="subtitle">
                Mohon maaf, survey untuk 
                <strong>{{ $aplikasi->nama_aplikasi ?? 'Aplikasi' }}</strong>
                saat ini tidak menerima respon.
            </p>
        </div>
    </div>
</body>
</html>