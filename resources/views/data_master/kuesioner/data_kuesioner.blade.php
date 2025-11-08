<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kuesioner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kuesioner.css') }}">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-50">
<div id="overlay" class="overlay"></div>
<div class="flex h-screen overflow-hidden">
    @include('components.sidebar')
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('components.topbar')
        <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6">
            <div class="mb-6">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Data Kuesioner</li>
                    </ol>
                </nav>

                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold fs-4">Data Kuesioner</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-plus me-2"></i>Tambah Data
                        </button>
                    </div>

                    <div id="alertContainer" class="mb-3"></div>

                    <div class="table-responsive mt-3">
    <table class="table table-striped table-bordered table-hover align-middle w-100 table-border-top" id="kuesioner-table">
        <thead class="table-light">
            <tr>
                <th class="text-center" width="5%">No</th>
                <th class="text-wrap">Pertanyaan</th>
                <th class="text-center" width="8%">Gambar</th>
                <th class="text-center" width="10%">Tipe</th>
                <th class="text-center" width="12%">Kategori</th>
                <th class="text-center" width="12%">Persepsi</th>
                <th class="text-center" width="10%">Skala</th>
                <th class="text-center" width="8%">Mandatory</th>
                <th class="text-center" width="8%">Urutan</th>
                <th class="text-center" width="15%">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

                    {{-- Modal Tambah --}}
                    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formTambah" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-plus me-2"></i>Tambah Kuesioner
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body modal-body-scrollable">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-question-circle me-2 text-primary"></i>Pertanyaan
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="pertanyaan" 
                                                         class="form-control" 
                                                         rows="3"
                                                         placeholder="Masukkan pertanyaan kuesioner..."
                                                         required></textarea>
                                                <div class="invalid-feedback">
                                                    Pertanyaan harus diisi.
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-image me-2 text-primary"></i>Gambar Ilustrasi
                                                </label>
                                                <div class="image-upload-container" id="uploadContainer">
                                                    <input type="file" name="gambar" id="gambar" class="d-none" accept="image/*">
                                                    <div class="upload-placeholder">
                                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                        <p class="mb-1">Klik atau drag & drop gambar di sini</p>
                                                        <p class="small text-muted mb-0">Format: JPG, PNG, GIF, SVG (Max: 2MB)</p>
                                                    </div>
                                                    <div class="image-preview-container d-none">
                                                        <img class="image-preview" id="imagePreview" src="" alt="Preview">
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeImage">
                                                                <i class="fas fa-trash me-1"></i>Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Format gambar tidak valid atau ukuran terlalu besar.
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-list-ul me-2 text-primary"></i>Tipe
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="tipe" class="form-select" required>
                                                    <option value="">-- Pilih Tipe --</option>
                                                    <option value="radio">Radio Button</option>
                                                    <option value="free_text">Free Text</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Silakan pilih tipe pertanyaan.
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="kategoriGroup">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-folder me-2 text-primary"></i>Kategori
                                                </label>
                                                <select name="kategori_id" class="form-select">
                                                    <option value="">-- Pilih Kategori --</option>
                                                    @foreach($kategoriList as $kategori)
                                                        <option value="{{ $kategori->id }}">
                                                            {{ $kategori->nama_kategori }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">
                                                    Silakan pilih kategori.
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="persepsiGroup">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-arrow-right me-2 text-primary"></i>Persepsi
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="persepsi" class="form-select">
                                                    <option value="">-- Pilih Persepsi --</option>
                                                    <option value="manfaat">Manfaat</option>
                                                    <option value="risiko">Risiko</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Silakan pilih persepsi.
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="skalaTypeGroup">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sliders-h me-2 text-primary"></i>Tipe Skala Jawaban
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="skala_type" class="form-select">
                                                    <option value="kualitas">Kualitas (Sangat Baik - Sangat Buruk)</option>
                                                    <option value="kepercayaan">Kepercayaan (Sangat Percaya - Tidak Percaya)</option>
                                                    <option value="kemudahan">Kemudahan (Sangat Mudah - Sangat Sulit)</option>
                                                    <option value="membantu">Kegunaan (Sangat Membantu - Tidak Membantu)</option>
                                                    <option value="manfaat">Manfaat (Sangat Bermanfaat - Tidak Bermanfaat)</option>
                                                    <option value="khawatir">Kekhawatiran (Sangat Khawatir - Sangat Tidak Khawatir)</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Silakan pilih tipe skala.
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Mandatory
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="is_mandatory" class="form-select" required>
                                                    <option value="1">Wajib</option>
                                                    <option value="0">Tidak Wajib</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sort-numeric-up me-2 text-primary"></i>Urutan
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" 
                                                       name="urutan" 
                                                       class="form-control" 
                                                       placeholder="1"
                                                       min="1"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Urutan harus diisi dan belum digunakan.
                                                </div>
                                                <small class="text-muted">Urutan harus unik dan berurutan</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Simpan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Modal Edit --}}
                    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formEdit" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title">
                                            <i class="fas fa-edit me-2"></i>Edit Kuesioner
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body modal-body-scrollable">
                                        <input type="hidden" name="id">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-question-circle me-2 text-warning"></i>Pertanyaan
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="pertanyaan" 
                                                         class="form-control" 
                                                         rows="3"
                                                         placeholder="Masukkan pertanyaan kuesioner..."
                                                         required></textarea>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-image me-2 text-warning"></i>Gambar Ilustrasi
                                                </label>
                                                <div class="image-upload-container" id="uploadContainerEdit">
                                                    <input type="file" name="gambar" id="gambarEdit" class="d-none" accept="image/*">
                                                    <div class="upload-placeholder">
                                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                        <p class="mb-1">Klik atau drag & drop gambar di sini</p>
                                                        <p class="small text-muted mb-0">Format: JPG, PNG, GIF, SVG (Max: 2MB)</p>
                                                    </div>
                                                    <div class="image-preview-container d-none">
                                                        <img class="image-preview" id="imagePreviewEdit" src="" alt="Preview">
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeImageEdit">
                                                                <i class="fas fa-trash me-1"></i>Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Format gambar tidak valid atau ukuran terlalu besar.
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-list-ul me-2 text-warning"></i>Tipe
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="tipe" class="form-select" required>
                                                    <option value="">-- Pilih Tipe --</option>
                                                    <option value="radio">Radio Button</option>
                                                    <option value="free_text">Free Text</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="kategoriGroupEdit">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-folder me-2 text-warning"></i>Kategori
                                                </label>
                                                <select name="kategori_id" class="form-select">
                                                    <option value="">-- Pilih Kategori --</option>
                                                    @foreach($kategoriList as $kategori)
                                                        <option value="{{ $kategori->id }}">
                                                            {{ $kategori->nama_kategori }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="persepsiGroupEdit">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-arrow-right me-2 text-warning"></i>Persepsi
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="persepsi" class="form-select">
                                                    <option value="">-- Pilih Persepsi --</option>
                                                    <option value="manfaat">Manfaat</option>
                                                    <option value="risiko">Risiko</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3 display-none" id="skalaTypeGroupEdit">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sliders-h me-2 text-warning"></i>Tipe Skala Jawaban
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="skala_type" class="form-select">
                                                    <option value="kualitas">Kualitas (Sangat Baik - Sangat Buruk)</option>
                                                    <option value="kepercayaan">Kepercayaan (Sangat Percaya - Tidak Percaya)</option>
                                                    <option value="kemudahan">Kemudahan (Sangat Mudah - Sangat Sulit)</option>
                                                    <option value="membantu">Kegunaan (Sangat Membantu - Tidak Membantu)</option>
                                                    <option value="manfaat">Manfaat (Sangat Bermanfaat - Tidak Bermanfaat)</option>
                                                    <option value="khawatir">Kekhawatiran (Sangat Khawatir - Sangat Tidak Khawatir)</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Mandatory
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="is_mandatory" class="form-select" required>
                                                    <option value="1">Wajib</option>
                                                    <option value="0">Tidak Wajib</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sort-numeric-up me-2 text-warning"></i>Urutan
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" 
                                                       name="urutan" 
                                                       class="form-control" 
                                                       placeholder="1"
                                                       min="1"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Urutan harus diisi dan belum digunakan.
                                                </div>
                                                <small class="text-muted">Urutan harus unik dan berurutan</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save me-2"></i>Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script nonce="{{ request()->get('csp_nonce') }}">
    window.kuesionerRoutes = {
        data: "{{ route('kuesioner.data') }}",
        store: "{{ route('kuesioner.store') }}",
        base: "{{ url('/dashboard/kuesioner') }}"
    };
</script>

<script src="{{ asset('js/utils/alert-helper.js') }}"></script>
<script src="{{ asset('js/utils/datatables-config.js') }}"></script>
<script src="{{ asset('js/utils/form-validation.js') }}"></script>
<script src="{{ asset('js/admin/kuesioner.js') }}"></script>
</body>
</html>