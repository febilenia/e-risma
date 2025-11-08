<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold fs-4">Master Kategori Kuesioner</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i>Tambah Kategori
        </button>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="mb-3"></div>

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

    <!-- Modal Tambah -->
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

    <!-- Modal Edit -->
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

    <!-- Modal Preview Pertanyaan -->
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
