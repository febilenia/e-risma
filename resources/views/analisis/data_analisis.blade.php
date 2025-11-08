<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analisis Kepuasan</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/analisis.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
              <li class="breadcrumb-item active">Analisis Kepuasan</li>
            </ol>
          </nav>

          <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
              <h2 class="fw-bold fs-4">Analisis Kepuasan Pengguna</h2>

              <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm filter-date-group">
                  <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                  <input type="text" id="filterDateRange" class="form-control date-range-input" placeholder="Pilih rentang tanggal..." readonly>
                  <button class="btn btn-outline-secondary" type="button" id="clearDateFilter" title="Hapus Filter">
                    <i class="fas fa-times"></i>
                  </button>
                </div>

                <button class="btn btn-success btn-sm btn-export-all" data-format="csv" title="Export Semua ke CSV">
                  <i class="fas fa-file-csv me-1"></i>Export CSV
                </button>
                <button class="btn btn-danger btn-sm btn-export-all" data-format="pdf" title="Export Semua ke PDF">
                  <i class="fas fa-file-pdf me-1"></i>Export PDF
                </button>
              </div>
            </div>

            <div class="mt-3">
              <table class="table table-striped table-bordered table-hover align-middle w-100 table-border-top" id="analisis-table">
                <thead class="table-light">
                  <tr>
                    <th class="text-center w-50px">No</th>
                    <th>Nama Aplikasi</th>
                    <th>Nama OPD</th>
                    <th class="text-center w-100px">Total Responden</th>
                    <th class="text-center w-80px">NKP</th>
                    <th class="text-center w-120px">Predikat</th>
                    <th class="text-center w-120px">Aksi</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <div class="modal fade" id="modalDetailAnalisis" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalDetailLabel">
            <i class="fas fa-chart-line me-2"></i>Detail Analisis Kepuasan
          </h5>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light" id="btnToggleFullscreen" title="Fullscreen">
              <i class="fas fa-expand"></i>
            </button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card border-primary">
                <div class="card-body">
                  <h6 class="card-subtitle mb-3 text-muted">
                    <i class="fas fa-info-circle me-2"></i>Informasi Aplikasi
                  </h6>
                  <table class="table table-borderless table-sm">
                    <tr>
                      <td class="w-40-percent"><strong>Nama Aplikasi:</strong></td>
                      <td id="detailNamaAplikasi">-</td>
                    </tr>
                    <tr>
                      <td><strong>Nama OPD:</strong></td>
                      <td id="detailNamaOpd">-</td>
                    </tr>
                    <tr>
                      <td><strong>Total Responden:</strong></td>
                      <td id="detailTotalResponden">-</td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card border-success">
                <div class="card-body">
                  <h6 class="card-subtitle mb-3 text-muted">
                    <i class="fas fa-calculator me-2"></i>Hasil Perhitungan
                  </h6>
                  <table class="table table-borderless table-sm">
                    <tr>
                      <td class="w-40-percent"><strong>NK (Keuntungan):</strong></td>
                      <td><span id="detailNK" class="text-primary fw-bold">0.00</span></td>
                    </tr>
                    <tr>
                      <td><strong>NB (Biaya):</strong></td>
                      <td><span id="detailNB" class="text-danger fw-bold">0.00</span></td>
                    </tr>
                    <tr>
                      <td><strong>Nilai NKP:</strong></td>
                      <td><span id="detailNilaiNkp" class="text-success fw-bold fs-5">0.00</span></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card border-info">
                <div class="card-body text-center">
                  <h6 class="card-subtitle mb-3 text-muted">
                    <i class="fas fa-award me-2"></i>Kesimpulan Kepuasan
                  </h6>
                  <span id="detailKesimpulan" class="badge bg-secondary p-3 fs-5">-</span>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card">
                <div class="card-header bg-light">
                  <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2 text-success"></i>
                    Distribusi Jawaban Pertanyaan Manfaat
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row g-3" id="chartManfaatContainer"></div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card">
                <div class="card-header bg-light">
                  <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2 text-danger"></i>
                    Distribusi Jawaban Pertanyaan Risiko
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row g-3" id="chartRisikoContainer"></div>
                </div>
              </div>
            </div>

            <div class="col-12" id="saranSection">
              <div class="card">
                <div class="card-header bg-light">
                  <h6 class="mb-0">
                    <i class="fas fa-comments me-2 text-info"></i>
                    Saran & Komentar Responden
                    <span class="badge bg-info ms-2" id="saranCount">0</span>
                  </h6>
                </div>
                <div class="card-body">
                  <div id="saranEmpty" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Belum ada saran atau komentar dari responden</p>
                  </div>
                  <div id="saranList" class="mt-3"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script src="{{ asset('js/utils/alert-helper.js') }}"></script>
<script src="{{ asset('js/utils/datatables-config.js') }}"></script>
<script src="{{ asset('js/admin/analisis.js') }}"></script>
</body>
</html>