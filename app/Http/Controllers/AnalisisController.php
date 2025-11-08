<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\Responden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AnalisisController extends Controller
{
    public function index()
    {
        return view('analisis.data_analisis');
    }

    public function data(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $user = session('user');

        $query = Aplikasi::with('opd')
            ->withCount(['responden' => function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                }
            }]);

        if ($user->role === 'admin_opd' && $user->opd_id) {
            $query->where('opd_id', $user->opd_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nama_opd', function ($row) {
                return $row->opd->nama_opd ?? '-';
            })
            ->addColumn('total_responden', function ($row) {
                return $row->responden_count;
            })
            ->addColumn('nkp', function ($row) use ($startDate, $endDate) {
                $hasil = $this->hitungNilaiKepuasan($row->id, null, $startDate, $endDate);
                return $hasil['has_data'] ? number_format($hasil['nkp'], 2) : '-';
            })
            ->addColumn('predikat', function ($row) use ($startDate, $endDate) {
                $hasil = $this->hitungNilaiKepuasan($row->id, null, $startDate, $endDate);
                $predikat = $hasil['predikat'];

                $badgeClass = 'bg-secondary';
                if ($predikat === 'Sangat Baik') $badgeClass = 'bg-primary';
                elseif ($predikat === 'Baik') $badgeClass = 'bg-success';
                elseif ($predikat === 'Cukup') $badgeClass = 'bg-warning text-dark';
                elseif ($predikat === 'Buruk') $badgeClass = 'badge-orange';
                elseif ($predikat === 'Sangat Buruk') $badgeClass = 'bg-danger';

                return '<span class="badge ' . $badgeClass . '">' . $predikat . '</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '
                <button class="btn btn-sm btn-info btn-detail" 
                        data-uid="' . $row->id_encrypt . '" 
                        title="Lihat Detail">
                    <i class="fas fa-eye text-white"></i>
                </button>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item btn-export-detail" href="#" 
                               data-uid="' . $row->id_encrypt . '" 
                               data-format="csv">
                                <i class="fas fa-file-csv me-2"></i>Export CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item btn-export-detail" href="#" 
                               data-uid="' . $row->id_encrypt . '" 
                               data-format="pdf">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </a>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['predikat', 'aksi'])
            ->make(true);
    }

    public function detail($uid, Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $aplikasi = Aplikasi::with('opd')->where('id_encrypt', $uid)->first();

            if (!$aplikasi) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aplikasi dengan UID tersebut tidak ditemukan.',
                ], 404);
            }

            $hasil = $this->hitungNilaiKepuasan($aplikasi->id, null, $startDate, $endDate);

            $totalRespondenQuery = Responden::where('aplikasi_id', $aplikasi->id);
            if ($startDate && $endDate) {
                $totalRespondenQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $totalRespondenQuery->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $totalRespondenQuery->whereDate('created_at', '<=', $endDate);
            }
            $totalResponden = $totalRespondenQuery->count();

            $hasData = $hasil['has_data'] ?? false;

            $saranList = $this->getSaranKomentar($aplikasi->id, null, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'uid' => $aplikasi->id_encrypt,
                'nama_aplikasi' => $aplikasi->nama_aplikasi ?? '-',
                'nama_opd' => optional($aplikasi->opd)->nama_opd ?? '-',
                'total_responden' => $totalResponden,
                'has_data' => $hasData,
                'nkp' => $hasData ? round($hasil['nkp'], 2) : 0,
                'nk' => $hasData ? round($hasil['nk'], 2) : 0,
                'nb' => $hasData ? round($hasil['nb'], 2) : 0,
                'predikat' => $hasil['predikat'],
                'distribusi_jawaban' => $hasData ? $this->getDistribusiJawabanPerPersepsi($aplikasi->id, null, $startDate, $endDate) : ['manfaat' => [], 'risiko' => []],
                'saran' => $saranList,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan saat memuat detail: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    private function hitungNilaiKepuasan($aplikasiId, $tahun = null, $startDate = null, $endDate = null)
    {
        $nkQuery = DB::table('jawaban')
            ->join('kuesioner', 'jawaban.kuesioner_id', '=', 'kuesioner.id')
            ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
            ->where('jawaban.aplikasi_id', $aplikasiId)
            ->where('kuesioner.tipe', 'radio')
            ->where('kuesioner.persepsi', 'manfaat');

        if ($startDate && $endDate) {
            $nkQuery->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $nkQuery->whereDate('responden.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $nkQuery->whereDate('responden.created_at', '<=', $endDate);
        } elseif ($tahun) {
            $nkQuery->whereYear('responden.created_at', $tahun);
        }

        $nkPerResponden = $nkQuery
            ->select('jawaban.responden_id', DB::raw('AVG(jawaban.skor) as rata_manfaat'))
            ->groupBy('jawaban.responden_id')
            ->pluck('rata_manfaat');

        $nbQuery = DB::table('jawaban')
            ->join('kuesioner', 'jawaban.kuesioner_id', '=', 'kuesioner.id')
            ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
            ->where('jawaban.aplikasi_id', $aplikasiId)
            ->where('kuesioner.tipe', 'radio')
            ->where('kuesioner.persepsi', 'risiko');

        if ($startDate && $endDate) {
            $nbQuery->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $nbQuery->whereDate('responden.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $nbQuery->whereDate('responden.created_at', '<=', $endDate);
        } elseif ($tahun) {
            $nbQuery->whereYear('responden.created_at', $tahun);
        }

        $nbPerResponden = $nbQuery
            ->select('jawaban.responden_id', DB::raw('AVG(jawaban.skor) as rata_risiko'))
            ->groupBy('jawaban.responden_id')
            ->pluck('rata_risiko');

        $hasData = $nkPerResponden->isNotEmpty() || $nbPerResponden->isNotEmpty();

        if (!$hasData) {
            return [
                'has_data' => false,
                'nk' => 0.0,
                'nb' => 0.0,
                'nkp' => 0.0,
                'predikat' => 'Belum Ada Data',
                'responden_count' => 0
            ];
        }

        $nk = $nkPerResponden->isNotEmpty() ? $nkPerResponden->avg() : 0.0;
        $nb = $nbPerResponden->isNotEmpty() ? $nbPerResponden->avg() : 0.0;
        $nkp = $nk - $nb;

        if ($nkp >= 2.4) {
            $predikat = 'Sangat Baik';
        } elseif ($nkp >= 1.0) {
            $predikat = 'Baik';
        } elseif ($nkp >= -0.8) {
            $predikat = 'Cukup';
        } elseif ($nkp >= -2.4) {
            $predikat = 'Buruk';
        } else {
            $predikat = 'Sangat Buruk';
        }

        $respondenCount = $nkPerResponden->count() > $nbPerResponden->count()
            ? $nkPerResponden->count()
            : $nbPerResponden->count();

        return [
            'has_data' => true,
            'nk' => $nk,
            'nb' => $nb,
            'nkp' => $nkp,
            'predikat' => $predikat,
            'responden_count' => $respondenCount
        ];
    }

    private function getDistribusiJawabanPerPersepsi($aplikasiId, $tahun = null, $startDate = null, $endDate = null)
    {
        $distribusi = [
            'manfaat' => [],
            'risiko' => []
        ];

        foreach (['manfaat', 'risiko'] as $persepsi) {
            $kuesionerList = DB::table('kuesioner')
                ->where('tipe', 'radio')
                ->where('persepsi', $persepsi)
                ->orderBy('urutan', 'asc')
                ->get();

            foreach ($kuesionerList as $kuesioner) {
                $query = DB::table('jawaban')
                    ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
                    ->where('jawaban.aplikasi_id', $aplikasiId)
                    ->where('jawaban.kuesioner_id', $kuesioner->id);

                if ($startDate && $endDate) {
                    $query->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $query->whereDate('responden.created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('responden.created_at', '<=', $endDate);
                } elseif ($tahun) {
                    $query->whereYear('responden.created_at', $tahun);
                }

                $skorCount = $query
                    ->select('jawaban.skor', DB::raw('COUNT(*) as count'))
                    ->groupBy('jawaban.skor')
                    ->pluck('count', 'skor')
                    ->toArray();

                $dataSkor = [];
                for ($i = 1; $i <= 5; $i++) {
                    $dataSkor[$i] = $skorCount[$i] ?? 0;
                }

                $totalJawaban = array_sum($dataSkor);

                if ($totalJawaban > 0) {
                    $kuesionerModel = \App\Models\Kuesioner::find($kuesioner->id);
                    $skalaLabels = $kuesionerModel ? $kuesionerModel->getSkalaLabelsArray() : [];

                    $aplikasi = \App\Models\Aplikasi::find($aplikasiId);
                    $namaAplikasi = $aplikasi->nama_aplikasi ?? '';
                    
                    $pertanyaanText = $kuesioner->pertanyaan;
                    $pertanyaanText = str_replace(
                        ['{aplikasi}', '{$nama_aplikasi}', '{@nama_aplikasi}'],
                        [$namaAplikasi, $namaAplikasi, $namaAplikasi],
                        $pertanyaanText
                    );
                    
                    $pertanyaanShort = strlen($pertanyaanText) > 60
                        ? substr($pertanyaanText, 0, 60) . '...'
                        : $pertanyaanText;

                    $distribusi[$persepsi][] = [
                        'urutan' => $kuesioner->urutan,
                        'pertanyaan' => $pertanyaanText,
                        'pertanyaan_short' => $pertanyaanShort,
                        'data_skor' => $dataSkor,
                        'skala_labels' => $skalaLabels
                    ];
                }
            }
        }

        return $distribusi;
    }

    public function exportDetail($uid, Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $tahun = $request->get('tahun');
            $format = $request->get('format', 'csv');

            $aplikasi = Aplikasi::with('opd')->where('id_encrypt', $uid)->firstOrFail();

            if ($format === 'pdf') {
                return $this->exportPDF($aplikasi, $tahun, $startDate, $endDate);
            } else {
                return $this->exportCSV($aplikasi, $tahun, $startDate, $endDate);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    private function exportCSV($aplikasi, $tahun = null, $startDate = null, $endDate = null)
    {
        $periodLabel = $this->getPeriodLabel($tahun, $startDate, $endDate);
        $filename = 'Analisis_' . str_replace(' ', '_', $aplikasi->nama_aplikasi) . '_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $hasil = $this->hitungNilaiKepuasan($aplikasi->id, $tahun, $startDate, $endDate);

        $respondenQuery = Responden::where('aplikasi_id', $aplikasi->id);
        if ($startDate && $endDate) {
            $respondenQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $respondenQuery->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $respondenQuery->whereDate('created_at', '<=', $endDate);
        } elseif ($tahun) {
            $respondenQuery->whereYear('created_at', $tahun);
        }
        $responden = $respondenQuery->get();

        $callback = function () use ($aplikasi, $hasil, $responden, $periodLabel) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['DETAIL ANALISIS KEPUASAN PENGGUNA']);
            fputcsv($file, ['']);
            fputcsv($file, ['Nama Aplikasi', $aplikasi->nama_aplikasi]);
            fputcsv($file, ['Nama OPD', $aplikasi->opd->nama_opd ?? '-']);
            fputcsv($file, ['Total Responden', $responden->count() . ' orang']);
            fputcsv($file, ['Periode', $periodLabel]);
            fputcsv($file, ['']);
            fputcsv($file, ['=== HASIL ANALISIS ===']);
            fputcsv($file, ['NK (Keuntungan)', number_format($hasil['nk'], 2)]);
            fputcsv($file, ['NB (Biaya)', number_format($hasil['nb'], 2)]);
            fputcsv($file, ['Nilai NKP', number_format($hasil['nkp'], 2)]);
            fputcsv($file, ['Predikat', $hasil['predikat']]);
            fputcsv($file, ['']);
            fputcsv($file, ['']);
            fputcsv($file, ['=== DAFTAR RESPONDEN ===']);
            fputcsv($file, ['No', 'Nama', 'Jenis Kelamin', 'Usia', 'No HP', 'Tanggal Survey']);

            $no = 1;
            foreach ($responden as $resp) {
                fputcsv($file, [
                    $no++,
                    $resp->nama,
                    $resp->jenis_kelamin,
                    $resp->usia,
                    $resp->no_hp,
                    $resp->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPDF($aplikasi, $tahun = null, $startDate = null, $endDate = null)
    {
        $periodLabel = $this->getPeriodLabel($tahun, $startDate, $endDate);
        $filename = 'Analisis_' . str_replace(' ', '_', $aplikasi->nama_aplikasi) . '_' . date('YmdHis') . '.pdf';

        $hasil = $this->hitungNilaiKepuasan($aplikasi->id, $tahun, $startDate, $endDate);

        $respondenQuery = Responden::where('aplikasi_id', $aplikasi->id);
        if ($startDate && $endDate) {
            $respondenQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $respondenQuery->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $respondenQuery->whereDate('created_at', '<=', $endDate);
        } elseif ($tahun) {
            $respondenQuery->whereYear('created_at', $tahun);
        }
        $responden = $respondenQuery->get();

        $profil = $this->getProfilResponden($aplikasi->id, $startDate, $endDate);
        $detailPertanyaan = $this->getDetailPertanyaanDenganDistribusi($aplikasi->id, $startDate, $endDate);

        $data = [
            'aplikasi' => $aplikasi,
            'responden' => $responden,
            'hasil' => $hasil,
            'tahun' => $tahun,
            'period_label' => $periodLabel,
            'total_responden' => $responden->count(),
            'profil' => $profil,
            'detail_pertanyaan' => $detailPertanyaan,
            'list_aplikasi' => [
                [
                    'nama_aplikasi' => $aplikasi->nama_aplikasi,
                    'nkp' => $hasil['nkp'],
                    'predikat' => $hasil['predikat']
                ]
            ]
        ];

        $pdf = Pdf::loadView('analisis.export_pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function export(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $tahun = $request->get('tahun');
            $format = $request->get('format', 'csv');

            $user = session('user');
            $aplikasiQuery = Aplikasi::with('opd');
            
            if ($user->role === 'admin_opd' && $user->opd_id) {
                $aplikasiQuery->where('opd_id', $user->opd_id);
            }
            
            $aplikasiList = $aplikasiQuery->get();
            $dataAplikasi = [];
            $totalNkAllApps = 0;
            $totalNbAllApps = 0;
            $totalRespondenDenganData = 0;
            $aplikasiIds = [];

            foreach ($aplikasiList as $aplikasi) {
                $hasil = $this->hitungNilaiKepuasan($aplikasi->id, $tahun, $startDate, $endDate);

                $totalRespondenQuery = Responden::where('aplikasi_id', $aplikasi->id);
                if ($startDate && $endDate) {
                    $totalRespondenQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $totalRespondenQuery->whereDate('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $totalRespondenQuery->whereDate('created_at', '<=', $endDate);
                } elseif ($tahun) {
                    $totalRespondenQuery->whereYear('created_at', $tahun);
                }
                $totalResponden = $totalRespondenQuery->count();

                if ($hasil['has_data']) {
                    $dataAplikasi[] = [
                        'aplikasi_id' => $aplikasi->id,
                        'nama_aplikasi' => $aplikasi->nama_aplikasi,
                        'nama_opd' => $aplikasi->opd->nama_opd ?? '-',
                        'total_responden' => $totalResponden,
                        'nk' => $hasil['nk'],
                        'nb' => $hasil['nb'],
                        'nkp' => $hasil['nkp'],
                        'predikat' => $hasil['predikat']
                    ];

                    $totalNkAllApps += $hasil['nk'] * $hasil['responden_count'];
                    $totalNbAllApps += $hasil['nb'] * $hasil['responden_count'];
                    $totalRespondenDenganData += $hasil['responden_count'];
                    $aplikasiIds[] = $aplikasi->id;
                }
            }

            $rataRataNKP = 0;
            if ($totalRespondenDenganData > 0) {
                $avgNk = $totalNkAllApps / $totalRespondenDenganData;
                $avgNb = $totalNbAllApps / $totalRespondenDenganData;
                $rataRataNKP = round($avgNk - $avgNb, 2);
            }

            if ($rataRataNKP >= 2.4) {
                $predikatRataRata = 'Sangat Baik';
            } elseif ($rataRataNKP >= 1.0) {
                $predikatRataRata = 'Baik';
            } elseif ($rataRataNKP >= -0.8) {
                $predikatRataRata = 'Cukup';
            } elseif ($rataRataNKP >= -2.4) {
                $predikatRataRata = 'Buruk';
            } else {
                $predikatRataRata = 'Sangat Buruk';
            }

            $totalRespondenQuery = Responden::query();
            if ($startDate && $endDate) {
                $totalRespondenQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $totalRespondenQuery->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $totalRespondenQuery->whereDate('created_at', '<=', $endDate);
            } elseif ($tahun) {
                $totalRespondenQuery->whereYear('created_at', $tahun);
            }

            $avgNk = $totalRespondenDenganData > 0 ? $totalNkAllApps / $totalRespondenDenganData : 0;
            $avgNb = $totalRespondenDenganData > 0 ? $totalNbAllApps / $totalRespondenDenganData : 0;

            $summary = [
                'rata_rata_nkp' => $rataRataNKP,
                'predikat' => $predikatRataRata,
                'total_aplikasi' => count($dataAplikasi),
                'total_responden' => $totalRespondenQuery->count(),
                'avg_nk' => round($avgNk, 2),
                'avg_nb' => round($avgNb, 2)
            ];

            $periodLabel = $this->getPeriodLabel($tahun, $startDate, $endDate);

            if ($format === 'pdf') {
                return $this->exportAllPDF($dataAplikasi, $summary, $periodLabel, $aplikasiIds, $startDate, $endDate);
            } else {
                return $this->exportAllCSV($dataAplikasi, $summary, $periodLabel);
            }
        } catch (\Throwable $e) {
            Log::error('Export all error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    private function exportAllCSV($dataAplikasi, $summary, $periodLabel)
    {
        $filename = 'Analisis_Semua_Aplikasi_' . date('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($dataAplikasi, $summary, $periodLabel) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['ANALISIS KEPUASAN PENGGUNA - KABUPATEN GRESIK']);
            fputcsv($file, ['']);
            fputcsv($file, ['Periode', $periodLabel]);
            fputcsv($file, ['Tanggal Export', date('d/m/Y H:i') . ' WIB']);
            fputcsv($file, ['']);
            fputcsv($file, ['=== RINGKASAN KABUPATEN GRESIK ===']);
            fputcsv($file, ['Total Aplikasi dengan Data', $summary['total_aplikasi']]);
            fputcsv($file, ['Total Responden', $summary['total_responden']]);
            fputcsv($file, ['Rata-rata NKP Se-Kabupaten', number_format($summary['rata_rata_nkp'], 2)]);
            fputcsv($file, ['Predikat Kepuasan', $summary['predikat']]);
            fputcsv($file, ['']);
            fputcsv($file, ['']);
            fputcsv($file, ['=== DETAIL PER APLIKASI ===']);
            fputcsv($file, ['No', 'Nama Aplikasi', 'Nama OPD', 'Total Responden', 'NK', 'NB', 'NKP', 'Predikat']);

            $no = 1;
            foreach ($dataAplikasi as $data) {
                fputcsv($file, [
                    $no++,
                    $data['nama_aplikasi'],
                    $data['nama_opd'],
                    $data['total_responden'],
                    number_format($data['nk'], 2),
                    number_format($data['nb'], 2),
                    number_format($data['nkp'], 2),
                    $data['predikat']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportAllPDF($dataAplikasi, $summary, $periodLabel, $aplikasiIds, $startDate, $endDate)
    {
        $filename = 'Analisis_Semua_Aplikasi_' . date('YmdHis') . '.pdf';

        $profil = $this->getProfilResponden($aplikasiIds, $startDate, $endDate);
        $detailPertanyaan = $this->getDetailPertanyaanDenganDistribusi($aplikasiIds, $startDate, $endDate);

        $user = session('user');
        if ($user->role === 'admin_opd' && $user->opd_id) {
            $opd = \App\Models\OPD::find($user->opd_id);
            $judulLaporan = $opd->nama_opd ?? 'OPD';
        } else {
            $judulLaporan = 'KABUPATEN GRESIK';
        }

        $data = [
            'dataAplikasi' => $dataAplikasi,
            'summary' => $summary,
            'period_label' => $periodLabel,
            'profil' => $profil,
            'detail_pertanyaan' => $detailPertanyaan,
            'list_aplikasi' => $dataAplikasi,
            'judul_laporan' => $judulLaporan
        ];

        $pdf = Pdf::loadView('analisis.export_all_pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    private function getSaranKomentar($aplikasiId, $tahun = null, $startDate = null, $endDate = null)
    {
        $query = DB::table('jawaban')
            ->join('kuesioner', 'jawaban.kuesioner_id', '=', 'kuesioner.id')
            ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
            ->where('jawaban.aplikasi_id', $aplikasiId)
            ->where('kuesioner.tipe', 'free_text')
            ->whereNotNull('jawaban.isi_teks')
            ->where('jawaban.isi_teks', '!=', '');

        if ($startDate && $endDate) {
            $query->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->whereDate('responden.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('responden.created_at', '<=', $endDate);
        } elseif ($tahun) {
            $query->whereYear('responden.created_at', $tahun);
        }

        $saran = $query
            ->select(
                'jawaban.isi_teks as saran',
                'responden.nama as nama_responden',
                'responden.created_at as tanggal',
                'kuesioner.pertanyaan as pertanyaan'
            )
            ->orderBy('responden.created_at', 'desc')
            ->get();

        return $saran->map(function ($item) use ($aplikasiId) {
            $aplikasi = \App\Models\Aplikasi::find($aplikasiId);
            $namaAplikasi = $aplikasi->nama_aplikasi ?? '';

            $pertanyaanText = $item->pertanyaan;
            $pertanyaanText = str_replace(
                ['{aplikasi}', '{$nama_aplikasi}', '{@nama_aplikasi}'],
                [$namaAplikasi, $namaAplikasi, $namaAplikasi],
                $pertanyaanText
            );

            return [
                'nama_responden' => $item->nama_responden ?? 'Anonim',
                'tanggal' => \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y H:i'),
                'pertanyaan' => $pertanyaanText,
                'saran' => $item->saran,
                'nama_aplikasi' => $namaAplikasi,
            ];
        })->toArray();
    }

    private function getPeriodLabel($tahun = null, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        } elseif ($startDate) {
            return 'Sejak ' . Carbon::parse($startDate)->format('d/m/Y');
        } elseif ($endDate) {
            return 'Sampai ' . Carbon::parse($endDate)->format('d/m/Y');
        } elseif ($tahun) {
            return "Tahun $tahun";
        }
        return 'Semua Periode';
    }

    private function getProfilResponden($aplikasiIds, $startDate = null, $endDate = null)
    {
        if (!is_array($aplikasiIds)) {
            $aplikasiIds = [$aplikasiIds];
        }

        $query = Responden::whereIn('aplikasi_id', $aplikasiIds);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $responden = $query->get();
        $total = $responden->count();

        if ($total === 0) {
            return [
                'jenis_kelamin' => [
                    'laki-laki' => ['jumlah' => 0, 'persentase' => 0],
                    'perempuan' => ['jumlah' => 0, 'persentase' => 0],
                ],
                'usia' => [
                    'kurang_20' => ['jumlah' => 0, 'persentase' => 0],
                    '20_30' => ['jumlah' => 0, 'persentase' => 0],
                    'lebih_30' => ['jumlah' => 0, 'persentase' => 0],
                ],
                'total' => 0
            ];
        }

        // ✅ FIX: Gunakan filter() bukan where() untuk Collection
        $lakiLaki = $responden->filter(function($item) {
            return strtolower($item->jenis_kelamin) === 'laki-laki';
        })->count();
        
        $perempuan = $responden->filter(function($item) {
            return strtolower($item->jenis_kelamin) === 'perempuan';
        })->count();

        $kurang20 = $responden->filter(function($item) {
            return $item->usia < 20;
        })->count();
        
        $usia2030 = $responden->filter(function($item) {
            return $item->usia >= 20 && $item->usia <= 30;
        })->count();
        
        $lebih30 = $responden->filter(function($item) {
            return $item->usia > 30;
        })->count();

        return [
            'jenis_kelamin' => [
                'laki-laki' => [
                    'jumlah' => $lakiLaki,
                    'persentase' => $total > 0 ? round(($lakiLaki / $total) * 100, 1) : 0
                ],
                'perempuan' => [
                    'jumlah' => $perempuan,
                    'persentase' => $total > 0 ? round(($perempuan / $total) * 100, 1) : 0
                ],
            ],
            'usia' => [
                'kurang_20' => [
                    'jumlah' => $kurang20,
                    'persentase' => $total > 0 ? round(($kurang20 / $total) * 100, 1) : 0
                ],
                '20_30' => [
                    'jumlah' => $usia2030,
                    'persentase' => $total > 0 ? round(($usia2030 / $total) * 100, 1) : 0
                ],
                'lebih_30' => [
                    'jumlah' => $lebih30,
                    'persentase' => $total > 0 ? round(($lebih30 / $total) * 100, 1) : 0
                ],
            ],
            'total' => $total
        ];
    }

    /**
     * ✅ BARU: Ambil detail pertanyaan dengan distribusi jawaban (berapa responden per skor)
     */
    private function getDetailPertanyaanDenganDistribusi($aplikasiIds, $startDate = null, $endDate = null)
    {
        if (!is_array($aplikasiIds)) {
            $aplikasiIds = [$aplikasiIds];
        }

        $detail = [
            'manfaat' => [],
            'risiko' => []
        ];

        foreach (['manfaat', 'risiko'] as $persepsi) {
            $kuesionerList = DB::table('kuesioner')
                ->where('tipe', 'radio')
                ->where('persepsi', $persepsi)
                ->orderBy('urutan', 'asc')
                ->get();

            foreach ($kuesionerList as $kuesioner) {
                foreach ($aplikasiIds as $aplikasiId) {
                    $query = DB::table('jawaban')
                        ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
                        ->where('jawaban.aplikasi_id', $aplikasiId)
                        ->where('jawaban.kuesioner_id', $kuesioner->id);

                    if ($startDate && $endDate) {
                        $query->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    } elseif ($startDate) {
                        $query->whereDate('responden.created_at', '>=', $startDate);
                    } elseif ($endDate) {
                        $query->whereDate('responden.created_at', '<=', $endDate);
                    }

                    // Hitung distribusi per skor
                    $skorCount = $query
                        ->select('jawaban.skor', DB::raw('COUNT(*) as count'))
                        ->groupBy('jawaban.skor')
                        ->pluck('count', 'skor')
                        ->toArray();

                    $totalJawaban = array_sum($skorCount);

                    if ($totalJawaban > 0) {
                        $aplikasi = \App\Models\Aplikasi::find($aplikasiId);
                        $namaAplikasi = $aplikasi->nama_aplikasi ?? '';

                        $pertanyaanText = $kuesioner->pertanyaan;
                        $pertanyaanText = str_replace(
                            ['{aplikasi}', '{$nama_aplikasi}', '{@nama_aplikasi}'],
                            [$namaAplikasi, $namaAplikasi, $namaAplikasi],
                            $pertanyaanText
                        );

                        // Ambil label skala dari kuesioner
                        $kuesionerModel = \App\Models\Kuesioner::find($kuesioner->id);
                        $skalaLabels = $kuesionerModel ? $kuesionerModel->getSkalaLabelsArray() : [];

                        // Format distribusi dengan label
                        $distribusiJawaban = [];
                        for ($i = 1; $i <= 5; $i++) {
                            $jumlah = $skorCount[$i] ?? 0;
                            $label = $skalaLabels[$i] ?? "Skor $i";
                            $distribusiJawaban[] = [
                                'skor' => $i,
                                'label' => $label,
                                'jumlah' => $jumlah
                            ];
                        }

                        $detail[$persepsi][] = [
                            'urutan' => $kuesioner->urutan,
                            'pertanyaan' => $pertanyaanText,
                            'distribusi' => $distribusiJawaban,
                            'total_jawaban' => $totalJawaban,
                            'aplikasi_id' => $aplikasiId,
                            'nama_aplikasi' => $namaAplikasi
                        ];
                    }
                }
            }
        }

        return $detail;
    }
}