(function() {
    'use strict';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let table;
    let currentStartDate = null;
    let currentEndDate = null;
    let dateRangePicker;

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
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'nama' },
            { data: 'jenis_kelamin', className: 'text-center' },
            { data: 'usia', className: 'text-center' },
            { data: 'no_hp', className: 'text-center' },
            { data: 'created_at', className: 'text-center' },
            { data: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.respondenRoutes.data,
                data: function(d) {
                    d.start_date = currentStartDate;
                    d.end_date = currentEndDate;
                },
                dataSrc: function(json) {
                    $('#totalResponden').text(json.recordsTotal || 0);
                    return json.data;
                },
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns
        });

        table = $('#responden-table').DataTable(options);
    }

    /**
     * Handle lihat jawaban
     */
    function handleLihatJawaban() {
        $(document).on('click', '.btn-lihat-jawaban', function() {
            const uidAplikasi = $(this).data('uid-aplikasi');
            const uidResponden = $(this).data('uid-responden');

            if (!uidAplikasi || !uidResponden) {
                showAlert('UID tidak valid', 'error');
                return;
            }

            $('#respondenNama, #respondenAplikasi, #respondenTanggal').text('-');
            $('#jawabanContainer').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat jawaban...</p></div>');
            $('#modalLihatJawaban').modal('show');

            $.ajax({
                url: `/dashboard/aplikasi/${uidAplikasi}/responden/${uidResponden}/jawaban`,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        $('#respondenNama').text(res.responden.nama || '-');
                        $('#respondenAplikasi').text(res.aplikasi.nama_aplikasi || '-');
                        $('#respondenTanggal').text(res.responden.created_at || '-');

                        let htmlJawaban = '';
                        if (res.jawaban && res.jawaban.length > 0) {
                            res.jawaban.forEach((j) => {
                                const badgeClass = j.type === 'radio' ? 'bg-primary' : 'bg-info';
                                const typeLabel = j.type === 'radio' ? 'Pilihan Ganda' : 'Isian Teks';
                                
                                htmlJawaban += `
                                    <div class="card mb-3 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0"><strong>Pertanyaan ${j.no}:</strong> ${escapeHtml(j.pertanyaan)}</h6>
                                                <span class="badge ${badgeClass}">${typeLabel}</span>
                                            </div>
                                            <div class="alert alert-light mb-0">
                                                <strong>Jawaban:</strong><br>
                                                ${j.type === 'radio' 
                                                    ? `<span class="text-primary fw-bold">${escapeHtml(j.label)}</span>` 
                                                    : `<span class="text-dark">${escapeHtml(j.isian || '-')}</span>`
                                                }
                                            </div>
                                        </div>
                                    </div>`;
                            });
                        } else {
                            htmlJawaban = '<div class="alert alert-warning">Belum ada jawaban untuk responden ini.</div>';
                        }
                        $('#jawabanContainer').html(htmlJawaban);
                    } else {
                        $('#jawabanContainer').html('<div class="alert alert-danger">Gagal memuat jawaban: ' + (res.message || 'Unknown error') + '</div>');
                    }
                },
                error: function(xhr) {
                    const errMsg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    $('#jawabanContainer').html('<div class="alert alert-danger">Error: ' + errMsg + '</div>');
                }
            });
        });
    }

    /**
     * Handle hapus responden
     */
    function handleHapusResponden() {
        $(document).on('click', '.btn-hapus-responden', function() {
            const uidAplikasi = $(this).data('uid-aplikasi');
            const uidResponden = $(this).data('uid-responden');

            if (!confirm('Yakin ingin menghapus responden ini beserta jawabannya?')) return;

            $.ajax({
                url: `/dashboard/aplikasi/${uidAplikasi}/responden/${uidResponden}`,
                method: 'DELETE',
                success: function(res) {
                    if (res.success) {
                        showAlert(res.message || 'Responden berhasil dihapus', 'success');
                        table.ajax.reload(null, false);
                    } else {
                        showAlert(res.message || 'Gagal menghapus responden', 'error');
                    }
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
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
        handleLihatJawaban();
        handleHapusResponden();
    }

    $(document).ready(init);

})();