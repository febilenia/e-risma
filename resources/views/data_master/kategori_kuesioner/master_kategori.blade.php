<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kategori Kuesioner - </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
    
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
                            <li class="breadcrumb-item active">Master Kategori Kuesioner</li>
                        </ol>
                    </nav>

                    <div id="alertContainer" class="mb-3"></div>

                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="fw-bold fs-4">Master Kategori Kuesioner</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="fas fa-plus me-2"></i>Tambah Kategori
                            </button>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" id="kategori-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Nama Kategori</th>
                                        <th class="text-center" width="15%">Jumlah Pertanyaan</th>
                                        <th class="text-center" width="20%">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="formTambah" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-plus me-2"></i>Tambah Kategori Kuesioner
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-tag me-2 text-primary"></i>Nama Kategori
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       name="nama_kategori" 
                                                       class="form-control" 
                                                       placeholder="Masukkan nama kategori (contoh: Kualitas Layanan, Kepuasan Pengguna)"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Nama kategori harus diisi dan unik.
                                                </div>
                                                <small class="form-text text-muted">Nama kategori harus unik dan tidak boleh kosong.</small>
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

                        <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="formEdit" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning text-dark">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>Edit Kategori Kuesioner
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-tag me-2 text-warning"></i>Nama Kategori
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       name="nama_kategori" 
                                                       class="form-control" 
                                                       placeholder="Masukkan nama kategori"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Nama kategori harus diisi dan unik.
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

                        <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title" id="previewModalLabel">
                                            <i class="fas fa-eye me-2"></i>Daftar Pertanyaan
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ul id="listPertanyaan" class="list-group">
                                            <li class="list-group-item d-flex align-items-center">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Memuat data...
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i>Tutup
                                        </button>
                                    </div>
                                </div>
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
    window.kategoriRoutes = {
        data: "{{ route('kategori-kuesioner.data') }}",
        store: "{{ route('kategori-kuesioner.store') }}",
        base: "{{ url('/dashboard/kategori-kuesioner') }}"
    };
</script>

<script src="{{ asset('js/utils/alert-helper.js') }}"></script>
<script src="{{ asset('js/utils/datatables-config.js') }}"></script>
<script src="{{ asset('js/admin/kategori.js') }}"></script>
</body>
</html>