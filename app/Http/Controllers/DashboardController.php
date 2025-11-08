<?php

namespace App\Http\Controllers;

use App\Models\OPD;
use App\Models\Aplikasi;
use App\Models\Kuesioner;
use App\Models\Responden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login');
        }

        $user = session('user');

        if ($user->role === 'admin_opd' && $user->opd_id) {
            // Admin OPD: hanya lihat data OPD mereka
            $jumlahOpd = 1;
            $jumlahAplikasi = Aplikasi::where('opd_id', $user->opd_id)->count();

            $aplikasiIds = Aplikasi::where('opd_id', $user->opd_id)->pluck('id');
            $jumlahResponden = Responden::whereIn('aplikasi_id', $aplikasiIds)->count();

            $jumlahKuesioner = 0;

            // ✅ BARU: Helper untuk text dynamic
            $opdName = $user->opd->nama_opd ?? 'OPD Anda';
            $isSuperadmin = false;
        } else {
            // Superadmin: lihat semua data
            $jumlahOpd = OPD::count();
            $jumlahAplikasi = Aplikasi::count();
            $jumlahKuesioner = Kuesioner::count();
            $jumlahResponden = Responden::count();

            // ✅ BARU: Helper untuk text dynamic
            $opdName = null;
            $isSuperadmin = true;
        }

        return view('dashboard', compact(
            'jumlahOpd',
            'jumlahAplikasi',
            'jumlahKuesioner',
            'jumlahResponden',
            'opdName',
            'isSuperadmin'
        ));
    }

    public function getAnalisisData(Request $request)
    {
        try {
            // ✅ UBAH: Dari tahun ke date range
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $tahun = $request->get('tahun'); // backward compatibility
            
            $user = session('user');

            // ✅ Filter aplikasi berdasarkan role
            $query = Aplikasi::with('opd');

            if ($user->role === 'admin_opd' && $user->opd_id) {
                $query->where('opd_id', $user->opd_id);
            }

            $aplikasiList = $query->get();

            $dataAnalisis = [];
            $nkpTertinggi = -999;
            $nkpTerendah = 999;
            $aplikasiTertinggi = null;
            $aplikasiTerendah = null;
            $totalRespondenSemua = 0;
            $belumAdaData = 0;

            $totalNkAllApps = 0;
            $totalNbAllApps = 0;
            $totalRespondenDenganData = 0;

            foreach ($aplikasiList as $aplikasi) {
                $hasil = $this->hitungNilaiKepuasan($aplikasi->id, $tahun, $startDate, $endDate);

                $totalRespondenQuery = Responden::where('aplikasi_id', $aplikasi->id);
                
                // ✅ Filter by date range
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
                $totalRespondenSemua += $totalResponden;

                if ($hasil['has_data']) {
                    $totalNkAllApps += $hasil['nk'] * $hasil['responden_count'];
                    $totalNbAllApps += $hasil['nb'] * $hasil['responden_count'];
                    $totalRespondenDenganData += $hasil['responden_count'];

                    // ✅ FIX: Bulatkan dulu nilai NKP untuk konsistensi
                    $nkpRounded = round($hasil['nkp'], 2);

                    if ($nkpRounded > $nkpTertinggi) {
                        $nkpTertinggi = $nkpRounded;
                        $aplikasiTertinggi = [
                            'nama_aplikasi' => $aplikasi->nama_aplikasi,
                            'nama_opd' => $aplikasi->opd->nama_opd ?? '-',
                            'nkp' => $nkpRounded,
                            'predikat' => $hasil['predikat']
                        ];
                    }

                    if ($nkpRounded < $nkpTerendah) {
                        $nkpTerendah = $nkpRounded;
                        $aplikasiTerendah = [
                            'nama_aplikasi' => $aplikasi->nama_aplikasi,
                            'nama_opd' => $aplikasi->opd->nama_opd ?? '-',
                            'nkp' => $nkpRounded,
                            'predikat' => $hasil['predikat']
                        ];
                    }

                    $dataAnalisis[] = [
                        'nama_aplikasi' => $aplikasi->nama_aplikasi,
                        'nama_opd' => $aplikasi->opd->nama_opd ?? '-',
                        'nkp' => number_format($nkpRounded, 2),
                        'predikat' => $hasil['predikat'],
                        'total_responden' => $totalResponden
                    ];
                } else {
                    $belumAdaData++;
                }
            }

            $rataNkp = 0;
            if ($totalRespondenDenganData > 0) {
                $avgNk = $totalNkAllApps / $totalRespondenDenganData;
                $avgNb = $totalNbAllApps / $totalRespondenDenganData;
                $rataNkp = round($avgNk - $avgNb, 2);  // ✅ FIX: Round untuk konsistensi
            }

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_aplikasi' => $aplikasiList->count(),
                    'rata_nkp' => $rataNkp,
                    'nkp_tertinggi' => $nkpTertinggi > -999 ? $nkpTertinggi : 0,
                    'nkp_terendah' => $nkpTerendah < 999 ? $nkpTerendah : 0,
                    'nama_aplikasi_tertinggi' => $aplikasiTertinggi['nama_aplikasi'] ?? '-',
                    'nama_opd_tertinggi' => $aplikasiTertinggi['nama_opd'] ?? '-',
                    'predikat_tertinggi' => $aplikasiTertinggi['predikat'] ?? '-',
                    'nama_aplikasi_terendah' => $aplikasiTerendah['nama_aplikasi'] ?? '-',
                    'nama_opd_terendah' => $aplikasiTerendah['nama_opd'] ?? '-',
                    'predikat_terendah' => $aplikasiTerendah['predikat'] ?? '-',
                    'total_responden' => $totalRespondenSemua,
                    'belum_ada_data' => $belumAdaData
                ],
                'data' => $dataAnalisis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function hitungNilaiKepuasan($aplikasiId, $tahun = null, $startDate = null, $endDate = null)
    {
        // Query untuk NK (Nilai Keuntungan/Manfaat)
        $nkQuery = DB::table('jawaban')
            ->join('kuesioner', 'jawaban.kuesioner_id', '=', 'kuesioner.id')
            ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
            ->where('jawaban.aplikasi_id', $aplikasiId)
            ->where('kuesioner.tipe', 'radio')
            ->where('kuesioner.persepsi', 'manfaat');

        // ✅ Filter by date range
        if ($startDate && $endDate) {
            $nkQuery->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $nkQuery->whereDate('responden.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $nkQuery->whereDate('responden.created_at', '<=', $endDate);
        } elseif ($tahun) {
            $nkQuery->whereYear('responden.created_at', $tahun);
        }

        // NK = rata-rata skor LANGSUNG (tanpa inversi)
        $nkPerResponden = $nkQuery
            ->select('jawaban.responden_id', DB::raw('AVG(jawaban.skor) as rata_manfaat'))
            ->groupBy('jawaban.responden_id')
            ->pluck('rata_manfaat');

        // Query untuk NB (Nilai Biaya/Risiko)
        $nbQuery = DB::table('jawaban')
            ->join('kuesioner', 'jawaban.kuesioner_id', '=', 'kuesioner.id')
            ->join('responden', 'jawaban.responden_id', '=', 'responden.id')
            ->where('jawaban.aplikasi_id', $aplikasiId)
            ->where('kuesioner.tipe', 'radio')
            ->where('kuesioner.persepsi', 'risiko');

        // ✅ Filter by date range
        if ($startDate && $endDate) {
            $nbQuery->whereBetween('responden.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $nbQuery->whereDate('responden.created_at', '>=', $startDate);
        } elseif ($endDate) {
            $nbQuery->whereDate('responden.created_at', '<=', $endDate);
        } elseif ($tahun) {
            $nbQuery->whereYear('responden.created_at', $tahun);
        }

        // NB = rata-rata skor LANGSUNG (tanpa inversi)
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

        // NKP = NK - NB
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

        // ✅ Hitung jumlah responden unik yang punya data
        $respondenCount = $nkPerResponden->count() > $nbPerResponden->count()
            ? $nkPerResponden->count()
            : $nbPerResponden->count();

        return [
            'has_data' => true,
            'nk' => $nk,
            'nb' => $nb,
            'nkp' => $nkp,
            'predikat' => $predikat,
            'responden_count' => $respondenCount // ✅ TAMBAHAN: jumlah responden untuk weighted average
        ];
    }
}