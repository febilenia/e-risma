<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Aplikasi</title>
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
                        <li class="breadcrumb-item active">Data Master Aplikasi</li>
                    </ol>
                </nav>

                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold fs-4">Data Aplikasi</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="fas fa-plus me-2"></i>Tambah Data
                        </button>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" id="aplikasi-table">
                            <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Nama Aplikasi</th>
                                <th>Nama OPD</th>
                                <th class="text-center" width="10%">Status</th>
                                <th class="text-center" width="20%">Link Survey</th>
                                <th class="text-center" width="10%">Responden</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formTambah" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-plus me-2"></i>Tambah Aplikasi
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-mobile-alt me-2 text-primary"></i>Nama Aplikasi
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="nama_aplikasi" class="form-control" placeholder="Masukkan nama aplikasi..." required>
                                                <div class="invalid-feedback">Nama aplikasi harus diisi.</div>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-building me-2 text-primary"></i>Nama OPD
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="opd_id" class="form-select" required>
                                                    <option value="">-- Pilih OPD --</option>
                                                    @foreach($list_opd as $opd)
                                                        <option value="{{ $opd->id }}">{{ $opd->nama_opd }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback">Silakan pilih OPD.</div>
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

                    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="formEdit" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title">
                                            <i class="fas fa-edit me-2"></i>Edit Aplikasi
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-mobile-alt me-2 text-warning"></i>Nama Aplikasi
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="nama_aplikasi" class="form-control" placeholder="Masukkan nama aplikasi..." required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-building me-2 text-warning"></i>Nama OPD
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <select name="opd_id" class="form-select" id="edit_opd_id" required>
                                                    <option value="">-- Pilih OPD --</option>
                                                    @foreach($list_opd as $opd)
                                                        <option value="{{ $opd->id }}">{{ $opd->nama_opd }}</option>
                                                    @endforeach
                                                </select>
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

                    <div id="alertContainer" class="alert-container-fixed position-fixed top-0 end-0"></div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script nonce="{{ request()->get('csp_nonce') }}">
    window.aplikasiRoutes = {
        index: "{{ route('aplikasi.index') }}",
        store: "{{ route('aplikasi.store') }}",
        base: "{{ url('/dashboard/aplikasi') }}"
    };
</script>

<script src="{{ asset('js/utils/alert-helper.js') }}"></script>
<script src="{{ asset('js/utils/datatables-config.js') }}"></script>
<script src="{{ asset('js/admin/aplikasi.js') }}"></script>
</body>
</html>