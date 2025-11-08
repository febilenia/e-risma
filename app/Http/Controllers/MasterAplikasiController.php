<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\OPD;
use App\Models\Jawaban;
use App\Models\Responden;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterAplikasiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = session('user');

            // ✅ Filter berdasarkan role
            $query = Aplikasi::with('opd')->latest();

            if ($user->role === 'admin_opd' && $user->opd_id) {
                $query->where('opd_id', $user->opd_id);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_opd', function ($row) {
                    return $row->opd->nama_opd ?? '-';
                })
                ->addColumn('link_survey', function ($row) {
                    if ($row->status === 'closed') {
                        $url   = route('survey.closed', ['uid' => $row->id_encrypt]);
                        $label = 'Halaman Closed';
                    } else {
                        $url   = route('survey', ['uid' => $row->id_encrypt]);
                        $label = 'Buka Survey';
                    }
                    return '<a href="' . $url . '" target="_blank" class="text-primary text-decoration-underline">' . $label . '</a>';
                })
                ->addColumn('responden', function ($row) {
                    $uid = $row->id_encrypt;
                    $url = route('aplikasi.responden', $uid);
                    return '<a href="' . $url . '" class="btn btn-info btn-sm" title="Lihat Responden">
                        <i class="fas fa-eye text-white"></i>
                    </a>';
                })
                ->addColumn('aksi', function ($row) {
                    $uid = $row->id_encrypt;

                    $editBtn = '<button class="btn btn-sm btn-warning btn-edit me-1" 
                                    data-uid="' . $uid . '" 
                                    title="Edit">
                                    <i class="fas fa-edit text-white"></i>
                                </button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger btn-delete me-1" 
                                    data-uid="' . $uid . '" 
                                    title="Hapus">
                                    <i class="fas fa-trash-alt text-white"></i>
                                </button>';

                    $isClosed   = ($row->status === 'closed');
                    $toggleIcon = $isClosed ? 'fa-lock' : 'fa-lock-open';
                    $toggleText = $isClosed ? 'Buka survey' : 'Tutup survey';
                    $btnClass   = $isClosed ? 'btn-secondary' : 'btn-success';

                    $toggleBtn = '<button class="btn ' . $btnClass . ' btn-sm btn-toggle-status" data-uid="' . $uid . '">
                                    <i class="fas ' . $toggleIcon . '"></i>
                                </button>';

                    return $editBtn . $deleteBtn . $toggleBtn;
                })
                ->rawColumns(['status', 'link_survey', 'responden', 'aksi'])
                ->make(true);
        }

        $user = session('user');

        // ✅ Filter OPD list berdasarkan role
        if ($user->role === 'admin_opd' && $user->opd_id) {
            $list_opd = OPD::where('id', $user->opd_id)->get();
        } else {
            $list_opd = OPD::all();
        }

        return view('data_master.aplikasi.index', compact('list_opd'));
    }

    public function viewMaster()
    {
        $user = session('user');

        // ✅ Filter OPD list berdasarkan role
        if ($user->role === 'admin_opd' && $user->opd_id) {
            $list_opd = OPD::where('id', $user->opd_id)->get();
        } else {
            $list_opd = OPD::all();
        }

        return view('data_master.aplikasi.master_aplikasi', compact('list_opd'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_aplikasi' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9\s\-\_\.]+$/u'
            ],
            'opd_id' => 'required|integer|exists:opd,id'
        ], [
            'nama_aplikasi.required' => 'Nama aplikasi harus diisi.',
            'nama_aplikasi.max' => 'Nama aplikasi maksimal 255 karakter.',
            'nama_aplikasi.regex' => 'Nama aplikasi hanya boleh mengandung huruf, angka, spasi, tanda hubung, underscore, dan titik.',
            'opd_id.required' => 'OPD harus dipilih.',
            'opd_id.integer' => 'OPD tidak valid.',
            'opd_id.exists' => 'OPD tidak ditemukan.',
        ]);

        $user = session('user');

        // ✅ Validasi OPD untuk admin OPD
        if ($user->role === 'admin_opd' && $user->opd_id) {
            if ($request->opd_id != $user->opd_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menambahkan aplikasi untuk OPD Anda sendiri'
                ], 403);
            }
        }

        $namaAplikasi = strip_tags($validated['nama_aplikasi']);
        $namaAplikasi = htmlspecialchars($namaAplikasi, ENT_QUOTES, 'UTF-8');
        $namaAplikasi = preg_replace('/[^A-Za-z0-9\s\-\_\.]/', '', $namaAplikasi);

        $aplikasi = Aplikasi::create([
            'nama_aplikasi' => trim($namaAplikasi),
            'opd_id' => $validated['opd_id'],
            'id_encrypt' => uniqid(),
            'link_survey' => '',
            'status' => 'open'
        ]);

        return response()->json(['success' => true, 'message' => 'Data aplikasi berhasil ditambahkan']);
    }

    public function edit($uid)
    {
        try {
            $user = session('user');
            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            // ✅ Validasi akses untuk admin OPD
            if ($user->role === 'admin_opd' && $user->opd_id) {
                if ($aplikasi->opd_id != $user->opd_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke aplikasi ini'
                    ], 403);
                }
            }

            return response()->json($aplikasi);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $uid)
    {
        try {
            // ✅ SECURITY: Validasi input dengan ketat
            $validated = $request->validate([
                'nama_aplikasi' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[A-Za-z0-9\s\-\_\.]+$/u' // ✅ SECURITY: Hanya huruf, angka, spasi, dash, underscore, dot
                ],
                'opd_id' => 'required|integer|exists:opd,id'
            ], [
                'nama_aplikasi.required' => 'Nama aplikasi harus diisi.',
                'nama_aplikasi.max' => 'Nama aplikasi maksimal 255 karakter.',
                'nama_aplikasi.regex' => 'Nama aplikasi hanya boleh mengandung huruf, angka, spasi, tanda hubung, underscore, dan titik.',
                'opd_id.required' => 'OPD harus dipilih.',
                'opd_id.integer' => 'OPD tidak valid.',
                'opd_id.exists' => 'OPD tidak ditemukan.',
            ]);

            $user = session('user');
            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            // ✅ Validasi akses untuk admin OPD
            if ($user->role === 'admin_opd' && $user->opd_id) {
                if ($aplikasi->opd_id != $user->opd_id || $request->opd_id != $user->opd_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah aplikasi ini'
                    ], 403);
                }
            }

            $namaAplikasi = strip_tags($validated['nama_aplikasi']);
            $namaAplikasi = htmlspecialchars($namaAplikasi, ENT_QUOTES, 'UTF-8');
            $namaAplikasi = preg_replace('/[^A-Za-z0-9\s\-\_\.]/', '', $namaAplikasi);


            $aplikasi->update([
                'nama_aplikasi' => trim($namaAplikasi),
                'opd_id' => $validated['opd_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data aplikasi berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($uid)
    {
        try {
            $user = session('user');
            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            // ✅ Validasi akses untuk admin OPD
            if ($user->role === 'admin_opd' && $user->opd_id) {
                if ($aplikasi->opd_id != $user->opd_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk menghapus aplikasi ini'
                    ], 403);
                }
            }

            $respondenCount = $aplikasi->responden()->count();
            if ($respondenCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus aplikasi yang sudah memiliki responden'
                ], 400);
            }

            $aplikasi->jawaban()->delete();
            $aplikasi->responden()->delete();
            $aplikasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data aplikasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($uid)
    {
        try {
            $user = session('user');
            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            // ✅ Validasi akses untuk admin OPD
            if ($user->role === 'admin_opd' && $user->opd_id) {
                if ($aplikasi->opd_id != $user->opd_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status aplikasi ini'
                    ], 403);
                }
            }

            $aplikasi->status = $aplikasi->status === 'open' ? 'closed' : 'open';
            $aplikasi->save();

            return response()->json([
                'success' => true,
                'new_status' => $aplikasi->status,
                'message' => 'Status survey berhasil diubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function responden($uid)
    {
        $user = session('user');
        $aplikasi = Aplikasi::with('opd')->where('id_encrypt', $uid)->firstOrFail();

        // ✅ Validasi akses untuk admin OPD
        if ($user->role === 'admin_opd' && $user->opd_id) {
            if ($aplikasi->opd_id != $user->opd_id) {
                return redirect()->route('aplikasi.master')->with('error', 'Anda tidak memiliki akses ke aplikasi ini');
            }
        }

        return view('data_master.aplikasi.responden', compact('aplikasi'));
    }

    public function respondenData($uid)
    {
        if (request()->ajax()) {
            $user = session('user');
            $aplikasi = Aplikasi::where('id_encrypt', $uid)->firstOrFail();

            // ✅ Validasi akses untuk admin OPD
            if ($user->role === 'admin_opd' && $user->opd_id) {
                if ($aplikasi->opd_id != $user->opd_id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }

            $validated = request()->validate([
                'start_date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
                'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date|before_or_equal:today',
            ], [
                'start_date.date_format' => 'Format tanggal mulai tidak valid.',
                'start_date.before_or_equal' => 'Tanggal mulai tidak boleh lebih dari hari ini.',
                'end_date.date_format' => 'Format tanggal akhir tidak valid.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
                'end_date.before_or_equal' => 'Tanggal akhir tidak boleh lebih dari hari ini.',
            ]);

            // ✅ TAMBAH: Date range filter
            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;

            $query = Responden::where('aplikasi_id', $aplikasi->id);

            // ✅ Filter by date range
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
                ->editColumn('nama', fn($row) => $row->nama)
                ->editColumn('no_hp', fn($row) => $row->no_hp ?? '-')
                ->editColumn('jenis_kelamin', fn($row) => $row->jenis_kelamin ?? '-')
                ->editColumn('created_at', fn($row) => $row->created_at->setTimezone('Asia/Jakarta')->format('d-m-Y H:i'))
                ->addColumn('aksi', function ($row) use ($uid) {
                    $respondenUid = $row->id_encrypt ?? $row->id;
                    $btnLihat = '<button class="btn btn-sm btn-info btn-lihat-jawaban me-1" 
                                data-uid-aplikasi="' . $uid . '" 
                                data-uid-responden="' . $respondenUid . '" 
                                title="Lihat Jawaban">
                                <i class="fas fa-eye text-white"></i>
                            </button>';
                    $btnHapus = '<button class="btn btn-sm btn-danger btn-hapus-responden" 
                                data-uid-aplikasi="' . $uid . '" 
                                data-uid-responden="' . $respondenUid . '" 
                                title="Hapus Responden">
                                <i class="fas fa-trash-alt text-white"></i>
                            </button>';
                    return $btnLihat . $btnHapus;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function create()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return redirect()->route('aplikasi.master');
    }

    public function getDetailJawaban()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return response()->json(['message' => 'Test method']);
    }
}
