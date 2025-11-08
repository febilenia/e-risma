<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Survey & Responden</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
              <i class="fas fa-home me-1"></i>Dashboard
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="{{ route('aplikasi.master') }}" class="text-decoration-none">Data Master Aplikasi</a>
          </li>
          <li class="breadcrumb-item active">Survey & Responden</li>
        </ol>
      </nav>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold fs-4 mb-0">
          <i class="fas fa-users me-2 text-primary"></i>Data Survey & Responden
        </h2>
        <a href="{{ route('aplikasi.master') }}" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
      </div>

      @php
        $isClosed  = ($aplikasi->status === 'closed');
        $surveyUrl = $isClosed
          ? route('survey.closed', ['uid' => $aplikasi->id_encrypt]) 
          : route('survey', ['uid' => $aplikasi->id_encrypt]);
      @endphp

      {{-- Info Aplikasi Card --}}
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-4">
              <div class="mb-1 text-muted small">Nama Aplikasi</div>
              <div class="fw-semibold">{{ $aplikasi->nama_aplikasi }}</div>
            </div>
            <div class="col-md-3">
              <div class="mb-1 text-muted small">Nama OPD</div>
              <div class="fw-semibold">{{ $aplikasi->opd->nama_opd ?? '-' }}</div>
            </div>
            <div class="col-md-2">
              <div class="mb-1 text-muted small">Status</div>
              <span class="badge {{ $isClosed ? 'bg-danger' : 'bg-success' }}">
                {{ $isClosed ? 'Closed' : 'Open' }}
              </span>
            </div>
            <div class="col-md-3">
              <div class="mb-1 text-muted small">Link Survey</div>
              <a href="{{ $surveyUrl }}" target="_blank" rel="noopener" class="text-primary text-decoration-underline">
                {{ $surveyUrl }}
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Header dengan Filter - Sama seperti Analisis --}}
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="fw-bold fs-5 mb-0">
          <i class="fas fa-table me-2 text-primary"></i>Daftar Responden
        </h5>
        
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <div class="input-group input-group-sm filter-date-group">
            <span class="input-group-text">
              <i class="fas fa-calendar-alt"></i>
            </span>
            <input type="text" 
                   id="filterDateRange" 
                   class="form-control date-range-input" 
                   placeholder="Pilih rentang tanggal..." 
                   readonly>
            <button class="btn btn-outline-secondary" 
                    type="button" 
                    id="clearDateFilter" 
                    title="Hapus Filter">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <span class="text-muted small">Total: <span id="totalResponden">-</span></span>
        </div>
      </div>

      {{-- Tabel Responden - Keluar dari Card --}}
      <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered table-hover align-middle nowrap w-100 table-border-top" id="responden-table">
          <thead class="table-light">
          <tr>
            <th class="text-center" width="5%">No</th>
            <th>Nama Responden</th>
            <th class="text-center" width="12%">Jenis Kelamin</th>
            <th class="text-center" width="8%">Usia</th>
            <th class="text-center" width="15%">No HP</th>
            <th class="text-center" width="15%">Tanggal Submit</th>
            <th class="text-center" width="12%">Aksi</th>
          </tr>
          </thead>
        </table>
      </div>

      <div id="alertContainer" class="position-fixed top-0 end-0 p-3 alert-container-fixed"></div>
    </main>
  </div>
</div>

<div class="modal fade" id="modalLihatJawaban" tabindex="-1" aria-labelledby="modalJawabanLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-file-alt me-2"></i>Detail Jawaban Responden
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="card border-primary mb-3">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <p class="mb-2"><strong>Nama Responden:</strong> <span id="respondenNama">-</span></p>
                <p class="mb-0"><strong>Aplikasi:</strong> <span id="respondenAplikasi">-</span></p>
              </div>
              <div class="col-md-6 text-md-end">
                <p class="mb-0"><strong>Tanggal Submit:</strong> <span id="respondenTanggal">-</span></p>
              </div>
            </div>
          </div>
        </div>

        <div id="jawabanContainer"></div>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script nonce="{{ request()->get('csp_nonce') }}">
    window.respondenRoutes = {
        data: "{{ route('aplikasi.responden.data', $aplikasi->id_encrypt) }}"
    };
</script>

<script src="{{ asset('js/utils/alert-helper.js') }}"></script>
<script src="{{ asset('js/utils/datatables-config.js') }}"></script>
<script src="{{ asset('js/admin/responden.js') }}"></script>
</body>
</html>