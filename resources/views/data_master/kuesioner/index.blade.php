<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold fs-4">Data Kuesioner</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i>Tambah Data
        </button>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="mb-3"></div>

    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" id="kuesioner-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th>Pertanyaan</th>
                    <th class="text-center" width="10%">Tipe</th>
                    <th class="text-center" width="12%">Kategori</th>
                    <th class="text-center" width="12%">Persepsi</th>
                    <th class="text-center" width="8%">Mandatory</th>
                    <th class="text-center" width="8%">Urutan</th>
                    <th class="text-center" width="15%">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formTambah" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-plus me-2"></i>Tambah Kuesioner
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Pertanyaan - Selalu tampil -->
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
                            
                            <!-- Tipe - Selalu tampil -->
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

                            <!-- Mandatory - Selalu tampil -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Wajib Diisi
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="is_mandatory" class="form-select" required>
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                            </div>

                            <!-- Kategori - Hanya untuk radio -->
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
                                <small class="text-muted">Opsional untuk pertanyaan radio</small>
                            </div>

                            <!-- Persepsi - Hanya untuk radio -->
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
                                    Silakan pilih persepsi untuk pertanyaan radio.
                                </div>
                            </div>
                            
                            <!-- Urutan - Selalu tampil -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-sort-numeric-up me-2 text-primary"></i>Urutan
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="urutan" 
                                       class="form-control" 
                                       placeholder="Masukkan nomor urutan (contoh: 1, 2, 3...)"
                                       min="1"
                                       required>
                                <div class="invalid-feedback">
                                    Urutan harus diisi dan belum digunakan.
                                </div>
                                <small class="text-muted">Urutan harus unik. Sistem akan mengecek otomatis.</small>
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Kuesioner
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row">
                            <!-- Pertanyaan - Selalu tampil -->
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
                            
                            <!-- Tipe - Selalu tampil -->
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

                            <!-- Mandatory - Selalu tampil -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Wajib Diisi
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="is_mandatory" class="form-select" required>
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                            </div>

                            <!-- Kategori - Hanya untuk radio -->
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
                                <small class="text-muted">Opsional untuk pertanyaan radio</small>
                            </div>

                            <!-- Persepsi - Hanya untuk radio -->
                            <div class="col-md-6 mb-3 display-none" id="persepsiGroupEdit">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-arrow-right me-2 text-warning"></i>Persepsi
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="persepsi" class="form-select">
                                    <option value="">-- Pilih Persepsi --</option>
                                    <option value="manfaat">Keuntungan dan Manfaat</option>
                                    <option value="risiko">Biaya dan Risiko</option>
                                </select>
                            </div>
                            
                            <!-- Urutan - Selalu tampil -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-sort-numeric-up me-2 text-warning"></i>Urutan
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="urutan" 
                                       class="form-control" 
                                       placeholder="Masukkan nomor urutan (contoh: 1, 2, 3...)"
                                       min="1"
                                       required>
                                <div class="invalid-feedback">
                                    Urutan harus diisi dan belum digunakan.
                                </div>
                                <small class="text-muted">Urutan harus unik. Sistem akan mengecek otomatis.</small>
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
