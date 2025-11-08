(function() {
    'use strict';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let table;
    let currentStartDate = null;
    let currentEndDate = null;
    let dateRangePicker;
    const chartInstances = { manfaat: [], risiko: [] };

    function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
    function initDateRangePicker() {
    dateRangePicker = flatpickr("#filterDateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        maxDate: "today", // cegah pilih hari depan
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                longhand: ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
            },
            months: {
                shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                longhand: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            },
        },
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                currentStartDate = formatLocalDate(selectedDates[0]);
                currentEndDate = formatLocalDate(selectedDates[1]);
                table.ajax.reload();
            } else if (selectedDates.length === 0) {
                currentStartDate = null;
                currentEndDate = null;
                table.ajax.reload();
            }
        }
    });
    }

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        const columns = [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_aplikasi' },
            { data: 'nama_opd' },
            { data: 'total_responden', className: 'text-center' },
            { data: 'nkp', className: 'text-center' },
            { data: 'predikat', className: 'text-center' },
            { data: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: '/dashboard/analisis/data',
                data: function(d) {
                    d.start_date = currentStartDate;
                    d.end_date = currentEndDate;
                },
                error: function(xhr) {
                    console.error('DataTables Error:', xhr.responseJSON);
                    showAlert('Gagal memuat data: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                }
            },
            columns: columns,
            order: [[1, 'asc']]
        });

        table = $('#analisis-table').DataTable(options);
    }

    /**
     * Handle detail analisis
     */
    function handleDetailAnalisis() {
        $(document).on('click', '.btn-detail', function() {
            const uid = $(this).data('uid');
            if (!uid) {
                showAlert('UID aplikasi tidak valid', 'error');
                return;
            }
            
            resetModalData();
            $('#modalDetailAnalisis').modal('show');
            
            $.ajax({
                url: `/dashboard/analisis/${uid}/detail`,
                method: 'GET',
                data: {
                    start_date: currentStartDate,
                    end_date: currentEndDate
                },
                success: function(d) {
                    if (d.success) {
                        $('#detailNamaAplikasi').text(d.nama_aplikasi || '-');
                        $('#detailNamaOpd').text(d.nama_opd || '-');
                        $('#detailTotalResponden').html(`<span class="badge bg-info">${d.total_responden} Responden</span>`);

                        if (d.has_data) {
                            $('#detailNK').text(parseFloat(d.nk).toFixed(2));
                            $('#detailNB').text(parseFloat(d.nb).toFixed(2));
                            $('#detailNilaiNkp').text(parseFloat(d.nkp).toFixed(2));

                            let badgeClass = 'bg-secondary';
                            if (d.predikat === 'Sangat Baik') badgeClass = 'bg-success';
                            else if (d.predikat === 'Baik') badgeClass = 'bg-primary';
                            else if (d.predikat === 'Cukup') badgeClass = 'bg-warning';
                            else if (d.predikat === 'Buruk') badgeClass = 'badge-orange';
                            else if (d.predikat === 'Sangat Buruk') badgeClass = 'bg-danger';

                            $('#detailKesimpulan').removeClass().addClass('badge p-3 fs-5 ' + badgeClass).text(d.predikat);

                            updateDetailCharts(d);
                        } else {
                            $('#detailKesimpulan').removeClass().addClass('badge bg-secondary p-3 fs-5').text('Belum Ada Data');
                            $('#chartManfaatContainer').html('<div class="col-12 text-center text-muted py-4">Belum ada data yang dapat ditampilkan</div>');
                            $('#chartRisikoContainer').html('<div class="col-12 text-center text-muted py-4">Belum ada data yang dapat ditampilkan</div>');
                        }
                    } else {
                        showAlert(d.error || 'Gagal memuat detail analisis', 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                    showAlert(errorMsg, 'error');
                    console.error('Detail Error:', xhr.responseJSON);
                }
            });
        });
    }

    /**
     * Handle export
     */
    function handleExport() {
        // Export detail
        $(document).on('click', '.btn-export-detail', function(e) {
            e.preventDefault();
            const uid = $(this).data('uid');
            const format = $(this).data('format');
            if (!uid) {
                showAlert('UID tidak valid', 'error');
                return;
            }
            showAlert(`Sedang memproses export ${format.toUpperCase()}...`, 'info');
            const params = new URLSearchParams({
                format: format,
                start_date: currentStartDate || '',
                end_date: currentEndDate || ''
            });
            window.location.href = `/dashboard/analisis/${uid}/export?${params.toString()}`;
        });

        // Export all
        $(document).on('click', '.btn-export-all', function(e) {
            e.preventDefault();
            const format = $(this).data('format');
            showAlert(`Sedang memproses export ${format.toUpperCase()}...`, 'info');
            const params = new URLSearchParams({
                format: format,
                start_date: currentStartDate || '',
                end_date: currentEndDate || ''
            });
            window.location.href = `/dashboard/analisis/export?${params.toString()}`;
        });
    }

    /**
     * Handle toggle fullscreen
     */
    function handleToggleFullscreen() {
        $(document).on('click', '#btnToggleFullscreen', function() {
            const modal = $('#modalDetailAnalisis');
            const icon = $(this).find('i');
            
            if (modal.hasClass('modal-fullscreen')) {
                modal.removeClass('modal-fullscreen');
                icon.removeClass('fa-compress').addClass('fa-expand');
                $(this).attr('title', 'Fullscreen');
            } else {
                modal.addClass('modal-fullscreen');
                icon.removeClass('fa-expand').addClass('fa-compress');
                $(this).attr('title', 'Exit Fullscreen');
            }
        });
    }

    /**
     * Reset modal data
     */
    function resetModalData() {
        $('#detailNamaAplikasi, #detailNamaOpd').text('-');
        $('#detailTotalResponden').html('-');
        $('#detailNK, #detailNB, #detailNilaiNkp').text('0.00');
        $('#detailKesimpulan').removeClass().addClass('badge bg-secondary p-3 fs-5').text('-');
        
        chartInstances.manfaat.forEach(chart => chart && chart.destroy());
        chartInstances.risiko.forEach(chart => chart && chart.destroy());
        chartInstances.manfaat = [];
        chartInstances.risiko = [];
        
        $('#chartManfaatContainer').empty();
        $('#chartRisikoContainer').empty();

        $('#saranList').empty().hide();
        $('#saranEmpty').show();
        $('#saranCount').text('0');
        $('#saranSection').show();
    }

    /**
     * Update detail charts
     */
    function updateDetailCharts(d) {
        const distribusi = d.distribusi_jawaban || { manfaat: [], risiko: [] };
        
        createMultipleCharts('chartManfaatContainer', distribusi.manfaat, 'manfaat');
        createMultipleCharts('chartRisikoContainer', distribusi.risiko, 'risiko');
        
        updateSaranSection(d.saran || []);
    }

    /**
     * Create multiple charts
     */
    function createMultipleCharts(containerId, data, type) {
        const container = $(`#${containerId}`);
        container.empty();

        if (!data || data.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-4">Tidak ada data untuk ditampilkan</div>');
            return;
        }

        const filteredData = data.filter(item => {
            if (!item.data_skor) return false;
            const total = Object.values(item.data_skor).reduce((sum, val) => sum + parseInt(val || 0), 0);
            return total > 0;
        });

        if (filteredData.length === 0) {
            container.html('<div class="col-12 text-center text-muted py-4">Tidak ada data untuk ditampilkan</div>');
            return;
        }

        filteredData.forEach((item, idx) => {
            const canvasId = `chart_${type}_${idx}`;
            
            const chartHtml = `
                <div class="col-md-6">
                    <div class="card chart-item">
                        <h6 title="${escapeHtml(item.pertanyaan)}">
                            <strong>P${item.urutan}:</strong> ${escapeHtml(item.pertanyaan)}
                        </h6>
                        <div class="chart-canvas-wrapper">
                            <canvas id="${canvasId}"></canvas>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(chartHtml);
            createSingleChart(canvasId, item, type);
        });
    }

    /**
     * Create single chart
     */
    function createSingleChart(canvasId, item, type) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        const skalaLabels = item.skala_labels || {};
        const labels = [
            skalaLabels[1] || 'Nilai 1',
            skalaLabels[2] || 'Nilai 2',
            skalaLabels[3] || 'Nilai 3',
            skalaLabels[4] || 'Nilai 4',
            skalaLabels[5] || 'Nilai 5'
        ];
        
        const dataValues = [
            item.data_skor[1] || 0,
            item.data_skor[2] || 0,
            item.data_skor[3] || 0,
            item.data_skor[4] || 0,
            item.data_skor[5] || 0
        ];

        let colors;
        if (type === 'manfaat') {
            colors = [
                { bg: 'rgba(220, 53, 69, 0.7)', border: 'rgb(220, 53, 69)' },
                { bg: 'rgba(253, 126, 20, 0.7)', border: 'rgb(253, 126, 20)' },
                { bg: 'rgba(255, 193, 7, 0.7)', border: 'rgb(255, 193, 7)' },
                { bg: 'rgba(25, 135, 84, 0.7)', border: 'rgb(25, 135, 84)' },
                { bg: 'rgba(13, 110, 253, 0.7)', border: 'rgb(13, 110, 253)' }
            ];
        } else {
            colors = [
                { bg: 'rgba(13, 110, 253, 0.7)', border: 'rgb(13, 110, 253)' },
                { bg: 'rgba(25, 135, 84, 0.7)', border: 'rgb(25, 135, 84)' },
                { bg: 'rgba(255, 193, 7, 0.7)', border: 'rgb(255, 193, 7)' },
                { bg: 'rgba(253, 126, 20, 0.7)', border: 'rgb(253, 126, 20)' },
                { bg: 'rgba(220, 53, 69, 0.7)', border: 'rgb(220, 53, 69)' }
            ];
        }

        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Jumlah Responden',
                data: dataValues,
                backgroundColor: colors.map(c => c.bg),
                borderColor: colors.map(c => c.border),
                borderWidth: 2
            }]
        };

        const chart = new Chart(canvas, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' responden';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { size: 9 },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            font: { size: 10 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                }
            }
        });

        chartInstances[type].push(chart);
    }

    /**
     * Update saran section
     */
    function updateSaranSection(saranList) {
        const saranContainer = $('#saranList');
        const saranEmpty = $('#saranEmpty');
        const saranCount = $('#saranCount');
        const saranSection = $('#saranSection');
        
        saranContainer.empty();
        saranSection.show();
        
        if (!saranList || saranList.length === 0) {
            saranEmpty.show();
            saranContainer.hide();
            saranCount.text('0');
            return;
        }
        
        saranEmpty.hide();
        saranContainer.show();
        saranCount.text(saranList.length);
        
        saranList.forEach((item, index) => {
            const saranHtml = `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-user-circle text-primary me-2"></i>
                                    <strong>${escapeHtml(item.nama_responden)}</strong>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    ${escapeHtml(item.tanggal)}
                                </small>
                            </div>
                            <span class="badge bg-info">#${index + 1}</span>
                        </div>
                        
                        <div class="alert alert-light mb-2 saran-alert-text">
                            <strong>Pertanyaan:</strong><br>
                            <span class="badge bg-primary me-2">${escapeHtml(item.nama_aplikasi || 'Aplikasi')}</span>
                            ${escapeHtml(item.pertanyaan)}
                        </div>
                        
                        <div class="p-3 bg-white border-start border-4 border-primary saran-box">
                            <i class="fas fa-quote-left text-muted me-2"></i>
                            <span class="saran-text-wrap">${escapeHtml(item.saran)}</span>
                            <i class="fas fa-quote-right text-muted ms-2"></i>
                        </div>
                    </div>
                </div>
            `;
            
            saranContainer.append(saranHtml);
        });
    }

    /**
     * Helper: Escape HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Initialize
     */
    function init() {
        initDateRangePicker();
        initDataTable();
        handleDetailAnalisis();
        handleExport();
        handleToggleFullscreen();
    }

    $(document).ready(init);

})();