<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold fs-4">Data Master OPD</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i>Tambah Data
        </button>
    </div>

    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" id="opd-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th class="text-wrap">Nama OPD</th>
                    <th class="text-center" width="15%">Total Aplikasi</th>
                    <th class="text-center" width="15%">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formTambah" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah OPD</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold"><i class="fas fa-building me-2 text-primary"></i>Nama OPD <span class="text-danger">*</span></label>
                                <input type="text" name="nama_opd" class="form-control" placeholder="Masukkan nama OPD..." required>
                                <div class="invalid-feedback">Nama OPD harus diisi.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formEdit" method="POST">@csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit OPD</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold"><i class="fas fa-building me-2 text-warning"></i>Nama OPD <span class="text-danger">*</span></label>
                                <input type="text" name="nama_opd" class="form-control" placeholder="Masukkan nama OPD..." required>
                                <div class="invalid-feedback">Nama OPD harus diisi.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-2"></i>Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="alertContainer" class="position-fixed top-0 end-0 p-3 alert-container-fixed"></div>
</div>
