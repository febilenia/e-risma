<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold fs-4">Data Master Admin OPD</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i>Tambah Data
        </button>
    </div>

    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" 
       id="admin-opd-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th class="text-wrap">OPD</th>
                    <th class="text-center" width="15%">Status Password</th>
                    <th class="text-center" width="12%">Terakhir Ganti</th>
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
                            <i class="fas fa-plus me-2"></i>Tambah Admin OPD
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-primary"></i>Nama Lengkap
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap..." required>
                                <div class="invalid-feedback">Nama lengkap harus diisi.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-2 text-primary"></i>Username
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" class="form-control" placeholder="username123" pattern="[a-z0-9_]+" required>
                                <small class="text-muted">Hanya huruf kecil, angka, dan underscore</small>
                                <div class="invalid-feedback">Username harus diisi.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-lock me-2 text-primary"></i>Password
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" class="form-control" placeholder="Min 8 karakter" required>
                                <small class="text-muted">Min 8 karakter, kombinasi huruf besar&kecil, angka, dan simbol</small>
                                <div class="invalid-feedback">Password harus diisi.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-building me-2 text-primary"></i>OPD
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="opd_id" class="form-select" required>
                                    <option value="">Pilih OPD...</option>
                                    @foreach($opdList ?? [] as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama_opd }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">OPD harus dipilih.</div>
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
                <input type="hidden" name="id">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Admin OPD
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user me-2 text-warning"></i>Nama Lengkap
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap..." required>
                                <div class="invalid-feedback">Nama lengkap harus diisi.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-id-card me-2 text-warning"></i>Username
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" class="form-control" placeholder="username123" pattern="[a-z0-9_]+" required>
                                <small class="text-muted">Hanya huruf kecil, angka, dan underscore</small>
                                <div class="invalid-feedback">Username harus diisi.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-building me-2 text-warning"></i>OPD
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="opd_id" class="form-select" required>
                                    <option value="">Pilih OPD...</option>
                                    @foreach($opdList ?? [] as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama_opd }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">OPD harus dipilih.</div>
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

    <!-- Modal Reset Password -->
    <div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetPasswordLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formResetPassword" method="POST">
                @csrf
                <input type="hidden" name="id">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Reset password untuk: <strong id="resetAdminName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-primary"></i>Password Baru
                                <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="new_password" class="form-control" placeholder="Min 8 karakter" required>
                            <small class="text-muted">Min 8 karakter, kombinasi huruf besar&kecil, angka, dan simbol</small>
                            <div class="invalid-feedback">Password baru harus diisi.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="position-fixed top-0 end-0 p-3 alert-container-fixed"></div>
</div>

