<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Kepuasan - {{ $judul_laporan }}</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf-export.css') }}">
</head>
<body>
    {{-- ===== HEADER ===== --}}
    <div class="header">
        <h2>
            LAPORAN HASIL SURVEI KEPUASAN PENGGUNA APLIKASI<br>
            {{ strtoupper($judul_laporan) }}
        </h2>
        <p>Pemerintah Kabupaten Gresik</p>
    </div>

    {{-- ===== INFO META ===== --}}
    <div class="info-meta">
        <div>
            <span class="info-label">Periode Survei</span>
            <span class="info-value">: {{ $period_label }}</span>
        </div>
        <div>
            <span class="info-label">Jumlah Aplikasi</span>
            <span class="info-value">: <strong>{{ $summary['total_aplikasi'] }} aplikasi</strong></span>
        </div>
        <div>
            <span class="info-label">Total Responden</span>
            <span class="info-value">: <strong>{{ $summary['total_responden'] }} orang</strong></span>
        </div>
    </div>

    {{-- ✅ CEK: Jika ada data aplikasi --}}
    @if($summary['total_aplikasi'] > 0)
        {{-- ===== NKP SUMMARY BOX (RATA-RATA) - UPDATED ===== --}}
        @php
            $predikat = $summary['predikat'];
            
            // ✅ Tentukan class CSS untuk predikat
            $predikatClass = 'sangat-buruk';
            if ($predikat === 'Sangat Baik') {
                $predikatClass = 'sangat-baik';
            } elseif ($predikat === 'Baik') {
                $predikatClass = 'baik';
            } elseif ($predikat === 'Cukup') {
                $predikatClass = 'cukup';
            } elseif ($predikat === 'Buruk') {
                $predikatClass = 'buruk';
            }
        @endphp

        {{-- ✅ FIXED: Background putih, border #1A3A8A --}}
        <div class="nkp-summary">
            <h3>RATA-RATA NILAI KEPUASAN PENGGUNA (NKP)</h3>
            <div class="nkp-value">{{ number_format($summary['rata_rata_nkp'], 2) }}</div>
            <div class="nkp-predikat {{ $predikatClass }}">
                {{ strtoupper($predikat) }}
            </div>
            <div class="nkp-meta">
                <span>Rata-rata NK: {{ number_format($summary['avg_nk'], 2) }}</span>
                <span>|</span>
                <span>Rata-rata NB: {{ number_format($summary['avg_nb'], 2) }}</span>
            </div>
        </div>

        {{-- ===== KETERANGAN PREDIKAT ===== --}}
        <div class="keterangan-predikat">
            <h4>KETERANGAN NILAI KEPUASAN PENGGUNA (NKP)</h4>
            <table>
                <tr>
                    <td class="label-col"><strong>Nilai NKP</strong></td>
                    <td class="range-col"><strong>Range</strong></td>
                    <td class="predikat-col"><strong>Predikat</strong></td>
                </tr>
                <tr>
                    <td class="label-col">< -2,4</td>
                    <td class="range-col">-4,0 s.d. < -2,4</td>
                    <td class="predikat-col">Sangat Buruk</td>
                </tr>
                <tr>
                    <td class="label-col">-2,4 s.d. < -0,8</td>
                    <td class="range-col">-2,4 s.d. < -0,8</td>
                    <td class="predikat-col">Buruk</td>
                </tr>
                <tr>
                    <td class="label-col">-0,8 s.d. < 1,0</td>
                    <td class="range-col">-0,8 s.d. < 1,0</td>
                    <td class="predikat-col">Cukup</td>
                </tr>
                <tr>
                    <td class="label-col">1,0 s.d. < 2,4</td>
                    <td class="range-col">1,0 s.d. < 2,4</td>
                    <td class="predikat-col">Baik</td>
                </tr>
                <tr>
                    <td class="label-col">≥ 2,4</td>
                    <td class="range-col">2,4 s.d. 4,0</td>
                    <td class="predikat-col">Sangat Baik</td>
                </tr>
            </table>
        </div>

        {{-- ===== 1. PROFIL RESPONDEN KESELURUHAN ===== --}}
        <div class="section-title">1. PROFIL RESPONDEN KESELURUHAN</div>
        
        <table>
            <thead>
                <tr>
                    <th class="w-25-percent">Kategori</th>
                    <th class="w-25-percent">Keterangan</th>
                    <th class="w-25-percent text-center">Jumlah</th>
                    <th class="w-25-percent text-center">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $jenisKelamin = $profil['jenis_kelamin'];
                    $usia = $profil['usia'];
                @endphp
                
                {{-- Jenis Kelamin --}}
                <tr>
                    <td rowspan="2" class="text-center" style="vertical-align: middle;"><strong>Jenis Kelamin</strong></td>
                    <td>Laki-laki</td>
                    <td class="text-center">{{ $jenisKelamin['laki-laki']['jumlah'] }}</td>
                    <td class="text-center">{{ number_format($jenisKelamin['laki-laki']['persentase'], 1) }}%</td>
                </tr>
                <tr>
                    <td>Perempuan</td>
                    <td class="text-center">{{ $jenisKelamin['perempuan']['jumlah'] }}</td>
                    <td class="text-center">{{ number_format($jenisKelamin['perempuan']['persentase'], 1) }}%</td>
                </tr>
                
                {{-- Usia --}}
                <tr>
                    <td rowspan="3" class="text-center" style="vertical-align: middle;"><strong>Usia</strong></td>
                    <td>&lt; 20 tahun</td>
                    <td class="text-center">{{ $usia['kurang_20']['jumlah'] }}</td>
                    <td class="text-center">{{ number_format($usia['kurang_20']['persentase'], 1) }}%</td>
                </tr>
                <tr>
                    <td>20-30 tahun</td>
                    <td class="text-center">{{ $usia['20_30']['jumlah'] }}</td>
                    <td class="text-center">{{ number_format($usia['20_30']['persentase'], 1) }}%</td>
                </tr>
                <tr>
                    <td>&gt; 30 tahun</td>
                    <td class="text-center">{{ $usia['lebih_30']['jumlah'] }}</td>
                    <td class="text-center">{{ number_format($usia['lebih_30']['persentase'], 1) }}%</td>
                </tr>
            </tbody>
        </table>

        {{-- ===== 2. RINGKASAN PER APLIKASI ===== --}}
        <div class="section-title">2. RINGKASAN NILAI KEPUASAN PENGGUNA PER APLIKASI</div>
        
        <table>
            <thead>
                <tr>
                    <th class="w-5-percent text-center">No</th>
                    <th class="w-26-percent">Nama Aplikasi</th>
                    <th class="w-24-percent">Nama OPD</th>
                    <th class="w-10-percent text-center">Responden</th>
                    <th class="w-10-percent text-center">NK</th>
                    <th class="w-10-percent text-center">NB</th>
                    <th class="w-10-percent text-center">NKP</th>
                    <th class="w-10-percent text-center">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataAplikasi as $index => $data)
                    @php
                        $predikatApp = $data['predikat'];
                        $predikatClass = 'predikat-sangat-buruk';
                        
                        if ($predikatApp === 'Sangat Baik') {
                            $predikatClass = 'predikat-sangat-baik';
                        } elseif ($predikatApp === 'Baik') {
                            $predikatClass = 'predikat-baik';
                        } elseif ($predikatApp === 'Cukup') {
                            $predikatClass = 'predikat-cukup';
                        } elseif ($predikatApp === 'Buruk') {
                            $predikatClass = 'predikat-buruk';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $data['nama_aplikasi'] }}</td>
                        <td>{{ $data['nama_opd'] }}</td>
                        <td class="text-center">{{ $data['total_responden'] }}</td>
                        <td class="text-center">{{ number_format($data['nk'], 2) }}</td>
                        <td class="text-center">{{ number_format($data['nb'], 2) }}</td>
                        <td class="text-center">{{ number_format($data['nkp'], 2) }}</td>
                        <td class="text-center">
                            <span class="predikat {{ $predikatClass }}">{{ $predikatApp }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ===== 3. DETAIL SURVEI PERSEPSI MANFAAT (PER APLIKASI) ===== --}}
        <div class="page-break"></div>
        <div class="section-title manfaat-section">3. DETAIL SURVEI - PERSEPSI KEUNTUNGAN DAN MANFAAT (PER APLIKASI)</div>
        
        @if(count($detail_pertanyaan['manfaat']) > 0)
            @php
                $currentApp = null;
            @endphp
            @foreach($detail_pertanyaan['manfaat'] as $index => $item)
                @if($currentApp !== $item['nama_aplikasi'])
                    @php
                        $currentApp = $item['nama_aplikasi'];
                    @endphp
                    <div class="section-subtitle">{{ strtoupper($currentApp) }}</div>
                @endif
                
                @php
                    $totalJawaban = $item['total_jawaban'];
                    $totalSkor = 0;
                    foreach($item['distribusi'] as $dist) {
                        $totalSkor += $dist['skor'] * $dist['jumlah'];
                    }
                    $rataManfaat = $totalJawaban > 0 ? $totalSkor / $totalJawaban : 0;
                    $nkpPertanyaan = $rataManfaat;
                    
                    if ($nkpPertanyaan >= 4.5) {
                        $predikatP = 'Sangat Baik';
                        $predikatClass = 'predikat-sangat-baik';
                    } elseif ($nkpPertanyaan >= 3.5) {
                        $predikatP = 'Baik';
                        $predikatClass = 'predikat-baik';
                    } elseif ($nkpPertanyaan >= 2.5) {
                        $predikatP = 'Cukup';
                        $predikatClass = 'predikat-cukup';
                    } elseif ($nkpPertanyaan >= 1.5) {
                        $predikatP = 'Buruk';
                        $predikatClass = 'predikat-buruk';
                    } else {
                        $predikatP = 'Sangat Buruk';
                        $predikatClass = 'predikat-sangat-buruk';
                    }
                @endphp
                
                <h4 style="margin-top: 18px; margin-bottom: 10px; font-size: 10px;">
                    <strong>P{{ $item['urutan'] }}:</strong> {{ $item['pertanyaan'] }}
                </h4>
                
                <table>
                    <thead>
                        <tr>
                            <th class="w-5-percent text-center">No</th>
                            <th class="w-35-percent">Pertanyaan</th>
                            <th class="w-8-percent text-center">Skor</th>
                            <th class="w-25-percent">Keterangan</th>
                            <th class="w-15-percent text-center">Jumlah Responden</th>
                            <th class="w-12-percent text-center">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['distribusi'] as $idx => $dist)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            @if($idx === 0)
                            <td rowspan="{{ count($item['distribusi']) }}" style="vertical-align: middle;">{{ $item['pertanyaan'] }}</td>
                            @endif
                            <td class="text-center">{{ $dist['skor'] }}</td>
                            <td>{{ $dist['label'] }}</td>
                            <td class="text-center">{{ $dist['jumlah'] }}</td>
                            @if($idx === 0)
                            <td class="text-center" rowspan="{{ count($item['distribusi']) }}" style="vertical-align: middle;">
                                <span class="predikat {{ $predikatClass }}">{{ $predikatP }}</span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        <tr class="table-border-top">
                            <td colspan="4" class="text-center"><strong>TOTAL</strong></td>
                            <td class="text-center"><strong>{{ $totalJawaban }}</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @else
            <p style="margin: 12px 0; color: #666;">Belum ada data survei untuk persepsi keuntungan dan manfaat.</p>
        @endif

        {{-- ===== 4. DETAIL SURVEI PERSEPSI RISIKO (PER APLIKASI) ===== --}}
        <div class="page-break"></div>
        <div class="section-title risiko-section">4. DETAIL SURVEI - PERSEPSI BIAYA DAN RISIKO (PER APLIKASI)</div>
        
        @if(count($detail_pertanyaan['risiko']) > 0)
            @php
                $currentApp = null;
            @endphp
            @foreach($detail_pertanyaan['risiko'] as $index => $item)
                @if($currentApp !== $item['nama_aplikasi'])
                    @php
                        $currentApp = $item['nama_aplikasi'];
                    @endphp
                    <div class="section-subtitle">{{ strtoupper($currentApp) }}</div>
                @endif
                
                @php
                    $totalJawaban = $item['total_jawaban'];
                    $totalSkor = 0;
                    foreach($item['distribusi'] as $dist) {
                        $totalSkor += $dist['skor'] * $dist['jumlah'];
                    }
                    $rataRisiko = $totalJawaban > 0 ? $totalSkor / $totalJawaban : 0;
                    $nkpPertanyaan = 6 - $rataRisiko;
                    
                    if ($nkpPertanyaan >= 4.5) {
                        $predikatP = 'Sangat Baik';
                        $predikatClass = 'predikat-sangat-baik';
                    } elseif ($nkpPertanyaan >= 3.5) {
                        $predikatP = 'Baik';
                        $predikatClass = 'predikat-baik';
                    } elseif ($nkpPertanyaan >= 2.5) {
                        $predikatP = 'Cukup';
                        $predikatClass = 'predikat-cukup';
                    } elseif ($nkpPertanyaan >= 1.5) {
                        $predikatP = 'Buruk';
                        $predikatClass = 'predikat-buruk';
                    } else {
                        $predikatP = 'Sangat Buruk';
                        $predikatClass = 'predikat-sangat-buruk';
                    }
                @endphp
                
                <h4 style="margin-top: 18px; margin-bottom: 10px; font-size: 10px;">
                    <strong>P{{ $item['urutan'] }}:</strong> {{ $item['pertanyaan'] }}
                </h4>
                
                <table>
                    <thead>
                        <tr>
                            <th class="w-5-percent text-center">No</th>
                            <th class="w-10-percent text-center">Skor</th>
                            <th class="w-50-percent">Keterangan</th>
                            <th class="w-20-percent text-center">Jumlah Responden</th>
                            <th class="w-15-percent text-center">Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['distribusi'] as $idx => $dist)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-center">{{ $dist['skor'] }}</td>
                            <td>{{ $dist['label'] }}</td>
                            <td class="text-center">{{ $dist['jumlah'] }}</td>
                            @if($idx === 0)
                            <td class="text-center" rowspan="{{ count($item['distribusi']) }}" style="vertical-align: middle;">
                                <span class="predikat {{ $predikatClass }}">{{ $predikatP }}</span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        <tr class="table-border-top">
                            <td colspan="3" class="text-center"><strong>TOTAL</strong></td>
                            <td class="text-center"><strong>{{ $totalJawaban }}</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @else
            <p style="margin: 12px 0; color: #666;">Belum ada data survei untuk persepsi biaya dan risiko.</p>
        @endif

    @else
        {{-- ✅ JIKA TIDAK ADA DATA APLIKASI SAMA SEKALI --}}
        <div class="hasil-box">
            <h3>⚠️ Belum Ada Data Survei</h3>
            <p>Belum ada aplikasi dengan data responden pada periode yang dipilih ({{ $period_label }}).</p>
            <p>Silakan pilih periode lain atau tunggu hingga ada responden yang mengisi survei.</p>
        </div>
    @endif
</body>
</html>