<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriKuesioner;
use Yajra\DataTables\Facades\DataTables;

class KategoriKuesionerController extends Controller
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

    public function index()
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        return view('data_master.kategori_kuesioner.master_kategori');
    }

    public function getData(Request $request)
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($request->ajax()) {
            $data = KategoriKuesioner::with('kuesioner')->latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('jumlah_pertanyaan', function ($row) {
                    return $row->kuesioner->count();
                })
                ->addColumn('aksi', function ($row) {
                    $jumlahPertanyaan = $row->kuesioner->count();
                    $previewButton = $jumlahPertanyaan > 0
                        ? '<button class="btn btn-info btn-sm btn-preview me-1" data-id="' . $row->id . '" data-nama="' . $row->nama_kategori . '" title="Preview Pertanyaan">
                            <i class="fas fa-eye"></i>
                        </button>'
                        : '<button class="btn btn-secondary btn-sm me-1" disabled title="Tidak ada pertanyaan">
                            <i class="fas fa-eye"></i>
                        </button>';

                    return $previewButton . '
                        <button class="btn btn-warning btn-sm btn-edit me-1" data-id="' . $row->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="' . $row->id . '" data-nama="' . $row->nama_kategori . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        // If not AJAX, redirect to index
        return $this->index();
    }

    public function store(Request $request)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_kuesioner,nama_kategori',
        ]);

        KategoriKuesioner::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
    }

    public function edit($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $kategori = KategoriKuesioner::findOrFail($id);
        return response()->json($kategori);
    }

    public function update(Request $request, $id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_kuesioner,nama_kategori,' . $id,
        ]);

        $kategori = KategoriKuesioner::findOrFail($id);
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
    }

    public function destroy($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $kategori = KategoriKuesioner::findOrFail($id);

        // Check if kategori has questions
        if ($kategori->kuesioner()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus kategori yang masih memiliki pertanyaan'], 400);
        }

        $kategori->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }

    public function getPertanyaan($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $kategori = KategoriKuesioner::findOrFail($id);
        $pertanyaan = $kategori->kuesioner()->select('id', 'pertanyaan')->get();

        return response()->json([
            'success' => true,
            'data' => $pertanyaan
        ]);
    }
}