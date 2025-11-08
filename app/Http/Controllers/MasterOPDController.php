<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OPD;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class MasterOPDController extends Controller
{
    /**
     * Manual authentication check
     */
    private function checkAuth()
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        return null;
    }

    /**
     * Display master OPD page
     */
    public function viewMaster()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        return view('data_master.opd.master_opd');
    }

    /**
     * Get OPD data for DataTables
     */
    public function index(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if ($request->ajax()) {
            // Ambil data dengan JOIN untuk count aplikasi
            $data = DB::table('opd')
                ->leftJoin('aplikasi', 'opd.id', '=', 'aplikasi.opd_id')
                ->select('opd.id', 'opd.nama_opd', DB::raw('COUNT(aplikasi.id) as total_aplikasi'))
                ->groupBy('opd.id', 'opd.nama_opd')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit me-1" data-id="' . $row->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" data-nama="' . $row->nama_opd . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return $this->viewMaster();
    }

    /**
     * Store new OPD
     */
    public function store(Request $request)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'nama_opd' => 'required|string|max:255|unique:opd,nama_opd'
        ]);

        OPD::create([
            'nama_opd' => $request->nama_opd
        ]);

        return response()->json(['message' => 'OPD berhasil ditambahkan!']);
    }

    /**
     * Get OPD for editing
     */
    public function edit($id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $opd = OPD::findOrFail($id);
        return response()->json($opd);
    }

    /**
     * Update OPD
     */
    public function update(Request $request, $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'nama_opd' => 'required|string|max:255|unique:opd,nama_opd,' . $id
        ]);

        $opd = OPD::findOrFail($id);
        $opd->update([
            'nama_opd' => $request->nama_opd
        ]);

        return response()->json(['message' => 'OPD berhasil diperbarui!']);
    }

    /**
     * Delete OPD
     */
    public function destroy($id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Cek apakah OPD punya aplikasi
        $countAplikasi = DB::table('aplikasi')->where('opd_id', $id)->count();
        
        if ($countAplikasi > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus OPD yang masih memiliki aplikasi'
            ], 400);
        }

        OPD::destroy($id);
        return response()->json(['message' => 'OPD berhasil dihapus!']);
    }

    public function create()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }
        
        return redirect()->route('opd.master');
    }
}