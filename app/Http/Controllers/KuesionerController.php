<?php

namespace App\Http\Controllers;

use App\Models\Kuesioner;
use App\Models\KategoriKuesioner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class KuesionerController extends Controller
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

        try {
            $kategoriList = KategoriKuesioner::all();
            return view('data_master.kuesioner.data_kuesioner', compact('kategoriList'));
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat halaman: ' . $e->getMessage());
        }
    }

    public function data()
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        try {
            $data = Kuesioner::with(['kategori'])
                ->orderBy('urutan')
                ->select([
                    'id',
                    'pertanyaan',
                    'tipe',
                    'persepsi',
                    'kategori_id',
                    'is_mandatory',
                    'urutan',
                    'gambar',
                    'skala_type',
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_mandatory', fn($q) => $q->is_mandatory ? 'Wajib' : 'Tidak')
                ->addColumn('persepsi', function ($q) {
                    return $q->tipe === 'radio'
                        ? ($q->persepsi ? ucfirst($q->persepsi) : '-')
                        : '-';
                })
                ->addColumn('kategori', function ($q) {
                    if ($q->tipe !== 'radio') return '-';
                    return $q->kategori?->nama_kategori ?? '-';
                })
                ->addColumn('skala_label', function ($q) {
                    if ($q->tipe !== 'radio' || !$q->skala_type) return '-';
                    $labels = [
                        'kualitas' => 'Kualitas(Sangat Baik-Sangat Buruk)',
                        'kemudahan' => 'Kemudahan(Sangat Mudah-Sangat Sulit)',
                        'membantu' => 'Kebergunaan(Sangat Membantu-Sangat Tidak Membantu)',
                        'manfaat' => 'Manfaat(Sangat Bermanfaat-Sangat Tidak Bermanfaat',
                        'kepercayaan' => 'Kepercayaan(Sangat Percaya-Sangat Tidak Percaya)',
                        'khawatir' => 'Kekhawatiran(Sangat Khawatir-Sangat Tidak Khawatir)'
                    ];
                    return $labels[$q->skala_type] ?? 'Kualitas';
                })
                ->addColumn('gambar_preview', function ($q) {
                    if (!$q->gambar) return '<span class="text-muted">Tidak ada gambar</span>';
                    return '<img src="' . asset('storage/' . $q->gambar) . '" class="img-thumbnail img-thumbnail-cover" width="50" height="50">';
                })
                ->editColumn('tipe', function ($q) {
                    $badges = [
                        'radio'     => '<span class="badge bg-primary">Radio</span>',
                        'free_text' => '<span class="badge bg-info">Free Text</span>',
                    ];
                    return $badges[$q->tipe] ?? e($q->tipe);
                })
                ->addColumn('aksi', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete ms-1" data-id="' . $row->id . '" data-pertanyaan="' . e(mb_strimwidth($row->pertanyaan, 0, 30, '...')) . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['aksi', 'tipe', 'gambar_preview'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@data: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memuat data'], 500);
        }
    }

    public function store(Request $request)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        try {
            // BASE RULES
            $rules = [
                'pertanyaan'   => [
                    'required',
                    'string',
                    'max:500',
                    'regex:/^[^<>{}]*$/',
                ],
                'tipe'         => 'required|string|in:radio,free_text',
                'is_mandatory' => 'required|boolean',
                'gambar'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // tambah validasi gambar
                'urutan'       => [
                    'required',
                    'integer',
                    'min:1',
                    Rule::unique('kuesioner', 'urutan'),
                ],
            ];

            // Tambahan untuk radio
            if ($request->tipe === 'radio') {
                $rules['persepsi']    = 'required_if:tipe,radio|in:manfaat,risiko';
                $rules['kategori_id'] = 'required_if:tipe,radio|exists:kategori_kuesioner,id';
                $rules['skala_type']  = 'required_if:tipe,radio|string|in:kualitas,kemudahan,membantu,manfaat,kepercayaan,khawatir';
            }

            $validated = $request->validate($rules, [
                'urutan.unique'          => 'Urutan sudah digunakan. Silakan pilih urutan yang berbeda.',
                'pertanyaan.required'    => 'Pertanyaan harus diisi.',
                'pertanyaan.regex'       => 'Pertanyaan tidak boleh mengandung karakter HTML atau script.',
                'tipe.required'          => 'Tipe pertanyaan harus dipilih.',
                'persepsi.required_if'   => 'Persepsi harus dipilih untuk tipe radio.',
                'persepsi.in'            => 'Persepsi tidak valid.',
                'kategori_id.required_if' => 'Kategori harus dipilih untuk tipe radio.',
                'kategori_id.exists'     => 'Kategori tidak valid.',
                'gambar.image'           => 'File harus berupa gambar.',
                'gambar.mimes'           => 'Gambar harus berformat: jpeg, png, jpg, gif, svg.',
                'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
            ]);

            $pertanyaan = strip_tags($validated['pertanyaan']);
            $pertanyaan = htmlspecialchars($pertanyaan, ENT_QUOTES, 'UTF-8');

            // Handle upload gambar
            $gambarPath = null;
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $extension = strtolower($file->getClientOriginalExtension());

                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExt)) {
                    return response()->json([
                        'message' => 'Tipe file tidak diizinkan. Hanya jpg, jpeg, png, gif, svg yang diperbolehkan.'
                    ], 422);
                }

                $fileName = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;

                // Pastikan folder ada
                $uploadPath = public_path('storage/kuesioner/images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Simpan langsung ke public/storage
                $file->move($uploadPath, $fileName);
                $gambarPath = 'kuesioner/images/' . $fileName;
            }

            // Normalisasi boolean
            $isMandatory = filter_var($validated['is_mandatory'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            if ($validated['tipe'] === 'radio') {
                $dataToInsert = [
                    'pertanyaan'   => $pertanyaan,
                    'tipe'         => 'radio',
                    'is_mandatory' => $isMandatory,
                    'urutan'       => $validated['urutan'],
                    'kategori_id'  => $validated['kategori_id'],
                    'persepsi'     => $validated['persepsi'],
                    'skala_type'   => $request->skala_type ?? 'kualitas',
                    'gambar'       => $gambarPath,
                ];
            } else { // free_text
                $dataToInsert = [
                    'pertanyaan'   => $pertanyaan,
                    'tipe'         => 'free_text',
                    'is_mandatory' => $isMandatory,
                    'urutan'       => $validated['urutan'],
                    'kategori_id'  => null,
                    'persepsi'     => null,
                    'skala_type'   => null,
                    'gambar'       => $gambarPath,
                ];
            }

            Log::info('Kuesioner@store payload', $dataToInsert);

            Kuesioner::create($dataToInsert);

            return response()->json(['message' => 'Data berhasil ditambahkan']);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@store: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        try {
            $data = Kuesioner::with('kategori')->findOrFail($id);
            return response()->json(['data' => $data]);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@edit: ' . $e->getMessage());
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        try {
            $data = Kuesioner::findOrFail($id);

            // BASE RULES
            $rules = [
                'pertanyaan'   => [
                    'required',
                    'string',
                    'max:500',
                    'regex:/^[^<>{}]*$/',
                ],
                'tipe'         => 'required|string|in:radio,free_text',
                'is_mandatory' => 'required|boolean',
                'gambar'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'urutan'       => [
                    'required',
                    'integer',
                    'min:1',
                    Rule::unique('kuesioner', 'urutan')->ignore($id),
                ],
            ];

            if ($request->tipe === 'radio') {
                $rules['persepsi']    = 'required_if:tipe,radio|in:manfaat,risiko';
                $rules['kategori_id'] = 'required_if:tipe,radio|exists:kategori_kuesioner,id';
                $rules['skala_type']  = 'required_if:tipe,radio|string|in:kualitas,kemudahan,membantu,manfaat,kepercayaan,khawatir';
            }

            $validated = $request->validate($rules, [
                'urutan.unique'           => 'Urutan sudah digunakan. Silakan pilih urutan yang berbeda.',
                'pertanyaan.required'     => 'Pertanyaan harus diisi.',
                'pertanyaan.regex'        => 'Pertanyaan tidak boleh mengandung karakter HTML atau script.',
                'tipe.required'           => 'Tipe pertanyaan harus dipilih.',
                'persepsi.required_if'    => 'Persepsi harus dipilih untuk tipe radio.',
                'persepsi.in'             => 'Persepsi tidak valid.',
                'kategori_id.required_if' => 'Kategori harus dipilih untuk tipe radio.',
                'kategori_id.exists'      => 'Kategori tidak valid.',
                'gambar.image'            => 'File harus berupa gambar.',
                'gambar.mimes'            => 'Gambar harus berformat: jpeg, png, jpg, gif, svg.',
                'gambar.max'              => 'Ukuran gambar maksimal 2MB.',
            ]);

            $pertanyaan = strip_tags($validated['pertanyaan']);
            $pertanyaan = htmlspecialchars($pertanyaan, ENT_QUOTES, 'UTF-8');

            // Handle upload gambar
            $gambarPath = $data->gambar; // keep existing image
            if ($request->hasFile('gambar')) {
                // Delete old image if exists
                if ($data->gambar) {
                    $oldImagePath = public_path('storage/' . $data->gambar);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $file = $request->file('gambar');
                $extension = strtolower($file->getClientOriginalExtension());

                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
                if (!in_array($extension, $allowedExt)) {
                    return response()->json([
                        'message' => 'Tipe file tidak diizinkan. Hanya jpg, jpeg, png, gif, svg yang diperbolehkan.'
                    ], 422);
                }

                $fileName = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;

                // Pastikan folder ada
                $uploadPath = public_path('storage/kuesioner/images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Simpan langsung ke public/storage
                $file->move($uploadPath, $fileName);
                $gambarPath = 'kuesioner/images/' . $fileName;
            }

            $isMandatory = filter_var($validated['is_mandatory'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            if ($validated['tipe'] === 'radio') {
                $updateData = [
                    'pertanyaan'   => $pertanyaan,
                    'tipe'         => 'radio',
                    'is_mandatory' => $isMandatory,
                    'urutan'       => $validated['urutan'],
                    'kategori_id'  => $validated['kategori_id'],
                    'persepsi'     => $validated['persepsi'],
                    'skala_type'   => $request->skala_type ?? 'kualitas',
                    'gambar'       => $gambarPath,
                ];
            } else {
                $updateData = [
                    'pertanyaan'   => $pertanyaan,
                    'tipe'         => 'free_text',
                    'is_mandatory' => $isMandatory,
                    'urutan'       => $validated['urutan'],
                    'kategori_id'  => null,
                    'persepsi'     => null,
                    'skala_type'   => null,
                    'gambar'       => $gambarPath,
                ];
            }

            $data->update($updateData);

            return response()->json(['message' => 'Data berhasil diperbarui']);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@update: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        // Check authentication
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        try {
            $data = Kuesioner::findOrFail($id);

            // Delete associated image - PERBAIKAN DISINI
            if ($data->gambar) {
                $imagePath = public_path('storage/' . $data->gambar);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $data->delete();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // API untuk cek ketersediaan urutan
    public function checkUrutan(Request $request)
    {
        if (!session('logged_in') || !session('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        try {
            $urutan    = $request->urutan;
            $excludeId = $request->exclude_id;

            $exists = Kuesioner::where('urutan', $urutan)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            return response()->json([
                'exists'  => $exists,
                'message' => $exists ? 'Urutan sudah digunakan' : 'Urutan tersedia'
            ]);
        } catch (Exception $e) {
            Log::error('Error in KuesionerController@checkUrutan: ' . $e->getMessage());
            return response()->json(['exists' => false]);
        }
    }
}
