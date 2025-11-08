<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Responden;
use App\Models\Aplikasi;
use App\Models\Jawaban;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class RespondenController extends Controller
{
    /**
     * Manual authentication check
     */
    private function checkAuth()
    {
        if (!\App\Http\Controllers\AuthController::isLoggedIn()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return null;
    }

    /**
     * Admin responden index (for general responden management)
     */
    public function index()
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Get all respondents or return view
        $responden = Responden::with('aplikasi')->latest()->get();
        return view('admin.responden.index', compact('responden'));
    }

    /**
     * Get responden data for specific aplikasi (DataTables)
     */
    public function respondenData(Request $request, $aplikasi_id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if ($request->ajax()) {
            $validated = $request->validate([
                'start_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
                'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date|before_or_equal:today',
            ], [
                'start_date.date_format' => 'Format tanggal mulai tidak valid.',
                'start_date.before_or_equal' => 'Tanggal mulai tidak boleh lebih dari hari ini.',
                'end_date.date_format' => 'Format tanggal akhir tidak valid.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
                'end_date.before_or_equal' => 'Tanggal akhir tidak boleh lebih dari hari ini.',
            ]);
            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;

            $query = Responden::where('aplikasi_id', $aplikasi_id);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $data = $query->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('usia', function ($row) {
                    return ($row->usia ?? 0) . ' tahun';
                })
                ->editColumn('jenis_kelamin', function ($row) {
                    return ucfirst($row->jenis_kelamin ?? '-');
                })
                ->editColumn('no_hp', function ($row) {
                    return $row->no_hp ?? '-';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->setTimezone('Asia/Jakarta')->format('d-m-Y H:i');
                })
                ->addColumn('aksi', function ($row) use ($aplikasi_id) {
                    return '
                    <button class="btn btn-info btn-sm btn-lihat-jawaban me-1" 
                        data-id="' . $row->id . '" 
                        data-aplikasi="' . $aplikasi_id . '"
                        title="Lihat Jawaban">
                        <i class="fas fa-eye text-white"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-hapus-responden" 
                        data-id="' . $row->id . '"
                        title="Hapus Responden">
                        <i class="fas fa-trash-alt text-white"></i>
                    </button>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        // If not AJAX, return view
        $aplikasi = Aplikasi::findOrFail($aplikasi_id);
        return view('admin.master_responden.index', compact('aplikasi'));
    }

    /**
     * Show responden jawaban detail - FIXED VERSION
     */
    public function jawabanResponden($uidAplikasi, $uidResponden)
    {
        // Check authentication for AJAX requests
        if (!session('logged_in') || !session('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
                'redirect' => '/login'
            ], 401);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $uidAplikasi)) {
            return response()->json([
                'success' => false,
                'message' => 'Format UID aplikasi tidak valid.'
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $uidResponden)) {
            return response()->json([
                'success' => false,
                'message' => 'Format UID responden tidak valid.'
            ], 400);
        }

        try {

            $aplikasi = Aplikasi::where('id_encrypt', $uidAplikasi)->firstOrFail();

            // ✅ Coba cari responden dengan beberapa cara
            $responden = null;

            // 1. Coba cari dengan id_encrypt dulu
            if (!empty($uidResponden) && !is_numeric($uidResponden)) {
                $responden = Responden::where('id_encrypt', $uidResponden)
                    ->where('aplikasi_id', $aplikasi->id)
                    ->first();
            }

            // 2. Jika tidak ketemu dan uidResponden adalah angka, cari dengan ID biasa
            if (!$responden && is_numeric($uidResponden)) {
                $responden = Responden::where('id', $uidResponden)
                    ->where('aplikasi_id', $aplikasi->id)
                    ->first();

                Log::info('Search by ID', [
                    'id' => $uidResponden,
                    'found' => $responden ? 'yes' : 'no'
                ]);
            }

            // 3. Jika masih tidak ketemu, throw error
            if (!$responden) {
                Log::error('Responden not found', [
                    'uid_aplikasi' => $uidAplikasi,
                    'uid_responden' => $uidResponden,
                    'aplikasi_id' => $aplikasi->id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Data responden tidak ditemukan'
                ], 404);
            }

            Log::info('Responden found', ['responden_id' => $responden->id]);

            // Ambil jawaban dengan relasi kuesioner
            $jawaban = Jawaban::with(['kuesioner' => function ($query) {
                $query->select('id', 'pertanyaan', 'tipe', 'urutan', 'skala_type', 'skala_labels', 'persepsi');
            }])
                ->where('responden_id', $responden->id)
                ->where('aplikasi_id', $aplikasi->id)
                ->get();

            Log::info('Jawaban fetched', ['count' => $jawaban->count()]);

            // Sort dan format jawaban
            $jawabanFormatted = $jawaban->sortBy(function ($item) {
                return $item->kuesioner->urutan ?? 0;
            })->values()->map(function ($item, $i) use ($aplikasi) {
                $kuesioner = $item->kuesioner;

                // ✅ PENTING: Buat label berdasarkan skor DAN persepsi
                $label = null;
                if ($item->skor !== null && $kuesioner) {
                    if ($kuesioner->tipe === 'radio') {
                        // ✅ Gunakan getSkalaLabelsArray() untuk mendapatkan label RAW (tanpa inversi)
                        // Karena ini untuk admin, tampilkan label sebenarnya dari database
                        $skalaLabels = $kuesioner->getSkalaLabelsArray();
                        $label = $skalaLabels[(int)$item->skor] ?? 'Nilai: ' . $item->skor;
                    } else {
                        $label = 'Nilai: ' . $item->skor;
                    }
                }

                // ✅ FIX: Replace placeholder {aplikasi}, {$nama_aplikasi}, dan {@nama_aplikasi} dengan nama aplikasi sebenarnya
                $pertanyaanText = $kuesioner->pertanyaan ?? 'Pertanyaan tidak tersedia';
                $pertanyaanText = str_replace(
                    ['{aplikasi}', '{$nama_aplikasi}', '{@nama_aplikasi}'],
                    [$aplikasi->nama_aplikasi, $aplikasi->nama_aplikasi, $aplikasi->nama_aplikasi],
                    $pertanyaanText
                );

                return [
                    'no' => $i + 1,
                    'pertanyaan' => $pertanyaanText,
                    'type' => $kuesioner->tipe ?? 'radio',
                    'persepsi' => $kuesioner->persepsi ?? null,
                    'nilai' => $item->skor,
                    'label' => $label,
                    'isian' => $item->isi_teks ?? null,
                    'skala_info' => [
                        'type' => $kuesioner->skala_type ?? null,
                        'custom_labels' => $kuesioner->skala_labels ?? null,
                        'persepsi' => $kuesioner->persepsi ?? null
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'responden' => [
                    'nama' => $responden->nama,
                    'created_at' => $responden->created_at->format('d/m/Y H:i:s'),
                ],
                'aplikasi' => [
                    'nama_aplikasi' => $aplikasi->nama_aplikasi,
                ],
                'jawaban' => $jawabanFormatted
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Model not found in jawabanResponden', [
                'uid_aplikasi' => $uidAplikasi,
                'uid_responden' => $uidResponden,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan. Pastikan aplikasi dan responden valid.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in jawabanResponden: ' . $e->getMessage(), [
                'uid_aplikasi' => $uidAplikasi,
                'uid_responden' => $uidResponden,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show jawaban for responden (single responden view)
     */
    public function jawaban($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $responden = Responden::with(['aplikasi', 'jawaban.kuesioner'])->findOrFail($id);
        return view('responden.jawaban', compact('responden'));
    }

    /**
     * Delete responden - FIXED VERSION
     */
    public function destroy($uidAplikasi, $uidResponden)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $uidAplikasi)) {
            return response()->json([
                'success' => false,
                'message' => 'Format UID aplikasi tidak valid.'
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $uidResponden)) {
            return response()->json([
                'success' => false,
                'message' => 'Format UID responden tidak valid.'
            ], 400);
        }

        try {
            $aplikasi = Aplikasi::where('id_encrypt', $uidAplikasi)->firstOrFail();

            // ✅ FIX: Coba cari dengan id_encrypt atau ID biasa
            $responden = null;

            if (!is_numeric($uidResponden)) {
                $responden = Responden::where('id_encrypt', $uidResponden)
                    ->where('aplikasi_id', $aplikasi->id)
                    ->first();
            }

            if (!$responden && is_numeric($uidResponden)) {
                $responden = Responden::where('id', $uidResponden)
                    ->where('aplikasi_id', $aplikasi->id)
                    ->first();
            }

            if (!$responden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Responden tidak ditemukan'
                ], 404);
            }

            $responden->jawaban()->delete();
            $responden->delete();

            return response()->json([
                'success' => true,
                'message' => 'Responden berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting responden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus responden: ' . $e->getMessage()
            ], 500);
        }
    }
}
