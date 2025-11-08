<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold fs-4">Data Aplikasi</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
      <i class="fas fa-plus me-2"></i>Tambah Data
    </button>
  </div>

  {{-- TABEL --}}
  <div class="mt-3">
    <table class="table table-striped table-bordered table-hover align-middle w-100 table-border-top"
       id="aplikasi-table">
      <thead class="table-light">
        <tr>
          <th class="text-center col-no">No</th>
          <th class="col-app">Nama Aplikasi</th>
          <th class="col-opd">Nama OPD</th>
          <th class="text-center col-status">Status</th>
          <th class="text-center col-link">Link Survey</th>
          <th class="text-center col-resp">Responden</th>
          <th class="text-center col-aksi">Aksi</th>
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

  <!-- Modal Edit -->
  <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="formEdit" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Aplikasi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="uid" id="edit_uid">
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
              <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold"><i class="fas fa-link me-2 text-warning"></i>Link Survey</label>
                <input type="text" name="link_survey" class="form-control" placeholder="Link akan otomatis ter-generate..." readonly>
                <small class="text-muted">Link survey akan otomatis dibuat oleh sistem.</small>
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

  <!-- Alert Container -->
  <div id="alertContainer" class="position-fixed top-0 end-0 p-3 alert-container-fixed"></div>
</div>

<script>
  window.Laravel = {
    routes: {
      aplikasiIndex: "{{ route('aplikasi.index') }}",
      aplikasiStore: "{{ route('aplikasi.store') }}",
      aplikasiBase: "{{ url('/dashboard/aplikasi') }}"
    }
  };
</script>