(function() {
    'use strict';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let table;

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        const columns = [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'nama_aplikasi' },
            { data: 'nama_opd' },
            { data: 'status', orderable: false, searchable: false, className: 'text-center' },
            {
                data: 'link_survey',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const baseUrl = window.location.origin;
                    const isClosed = (row.status && typeof row.status === 'string' && /closed/i.test(row.status));
                    const href = isClosed 
                        ? `${baseUrl}/${row.id_encrypt}/closed`
                        : `${baseUrl}/${row.id_encrypt}`;
                    return `<a href="${href}" target="_blank" rel="noopener" class="text-primary text-decoration-underline">${href}</a>`;
                }
            },
            {
                data: 'responden',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<a href="/dashboard/aplikasi/${row.id_encrypt}/responden" class="btn btn-info btn-sm">
                                <i class="fas fa-eye text-white"></i>
                            </a>`;
                }
            },
            {
                data: 'aksi',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const uid = row.id_encrypt;
                    const isClosed = (row.status === 'closed' || 
                               (typeof row.status === 'string' && /closed/i.test(row.status)));
                    const toggleIcon = isClosed ? 'fa-lock' : 'fa-lock-open';
                    const toggleText = isClosed ? 'Buka survey' : 'Tutup survey';
                    const btnClass = isClosed ? 'btn-secondary' : 'btn-success';

                    return `
                        <button class="btn btn-sm btn-warning btn-edit me-1" data-uid="${uid}">
                            <i class="fas fa-edit text-white"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete me-1" data-uid="${uid}">
                            <i class="fas fa-trash-alt text-white"></i>
                        </button>
                        <button class="btn ${btnClass} btn-sm btn-toggle-status" 
                                data-uid="${uid}"
                                data-status="${row.status}"
                                title="${toggleText}">
                            <i class="fas ${toggleIcon}"></i>
                        </button>
                    `;
                }
            }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.aplikasiRoutes.index,
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns
        });

        table = $('#aplikasi-table').DataTable(options);
    }

    /**
     * Handle form tambah
     */
    function handleFormTambah() {
        $('#formTambah').on('submit', function(e) {
            e.preventDefault();
            
            const namaAplikasi = $('input[name="nama_aplikasi"]').val().trim();
            const opdId = $('select[name="opd_id"]').val();
            
            if (!namaAplikasi) {
                showAlert('Nama aplikasi harus diisi', 'error');
                return;
            }
            
            if (!opdId) {
                showAlert('OPD harus dipilih', 'error');
                return;
            }
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
            
            $.post(window.aplikasiRoutes.store, $(this).serialize())
                .done(function(response) {
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Data aplikasi berhasil ditambahkan!', 'success');
                })
                .fail(function(xhr) {
                    let msg = 'Gagal menyimpan data.';
                    if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                    else if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    showAlert(msg, 'error');
                })
                .always(function() {
                    submitBtn.prop('disabled', false).html(originalText);
                });
        });
    }

    /**
     * Handle edit
     */
    function handleEdit() {
        $(document).on('click', '.btn-edit', function() {
            const uid = $(this).data('uid');
            if (!uid) {
                showAlert('UID aplikasi tidak valid', 'error');
                return;
            }
            
            $.get(`${window.aplikasiRoutes.base}/${uid}/edit`)
                .done(function(data) {
                    $('#modalEdit input[name=id]').val(data.id_encrypt);
                    $('#modalEdit input[name=nama_aplikasi]').val(data.nama_aplikasi);
                    $('#modalEdit select[name=opd_id]').val(data.opd_id);
                    $('#modalEdit').modal('show');
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal memuat data untuk diedit.';
                    showAlert(msg, 'error');
                });
        });
    }

    /**
     * Handle form edit
     */
    function handleFormEdit() {
        $('#formEdit').on('submit', function(e) {
            e.preventDefault();
            
            const namaAplikasi = $('#modalEdit input[name="nama_aplikasi"]').val().trim();
            const opdId = $('#modalEdit select[name="opd_id"]').val();
            
            if (!namaAplikasi) {
                showAlert('Nama aplikasi harus diisi', 'error');
                return;
            }
            
            if (!opdId) {
                showAlert('OPD harus dipilih', 'error');
                return;
            }
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const uid = $('#formEdit input[name=id]').val();
            
            if (!uid) {
                showAlert('UID aplikasi tidak valid', 'error');
                return;
            }
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...');

            $.ajax({
                url: `${window.aplikasiRoutes.base}/${uid}`,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalEdit').modal('hide');
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Data aplikasi berhasil diperbarui!', 'success');
                },
                error: function(xhr) {
                    let msg = 'Gagal mengupdate data.';
                    if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                    else if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    showAlert(msg, 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    }

    /**
     * Handle delete
     */
    function handleDelete() {
        $(document).on('click', '.btn-delete', function() {
            const uid = $(this).data('uid');
            
            if (!uid) {
                showAlert('UID aplikasi tidak ditemukan', 'error');
                return;
            }
            
            const rowData = table.row($(this).closest('tr')).data();
            const nama = rowData ? rowData.nama_aplikasi : 'aplikasi ini';
            
            if (confirm(`Yakin ingin menghapus "${nama}"?\n\nData yang dihapus tidak dapat dikembalikan.`)) {
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: `${window.aplikasiRoutes.base}/${uid}`,
                    method: 'DELETE',
                    timeout: 10000,
                    success: function(response) {
                        table.ajax.reload(null, false);
                        showAlert(response.message || 'Data berhasil dihapus!', 'success');
                    },
                    error: function(xhr, status) {
                        let message = 'Gagal menghapus data.';
                        
                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            message = 'Data tidak ditemukan.';
                        } else if (xhr.status === 400) {
                            message = 'Data masih memiliki relasi dengan data lain.';
                        } else if (xhr.status === 500) {
                            message = 'Terjadi kesalahan server.';
                        } else if (status === 'timeout') {
                            message = 'Request timeout. Silakan coba lagi.';
                        }
                        
                        showAlert(message, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });
    }

    /**
     * Handle toggle status
     */
    function handleToggleStatus() {
        $(document).on('click', '.btn-toggle-status', function() {
            const uid = $(this).data('uid');
            if (!uid) {
                showAlert('UID aplikasi tidak valid', 'error');
                return;
            }
            
            const rowData = table.row($(this).closest('tr')).data() || {};
            const isClosed = (rowData.status && typeof rowData.status === 'string' && /closed/i.test(rowData.status))
                             || rowData.status === 'closed';
            const confirmMsg = isClosed
                ? 'Yakin ingin membuka survey ini?'
                : 'Yakin ingin menutup survey ini?';

            if (!confirm(confirmMsg)) return;

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.post(`${window.aplikasiRoutes.base}/${uid}/toggle-status`)
                .done(function(response) {
                    if (response?.success) {
                        table.ajax.reload(null, false);
                        const msg = response.new_status === 'open' ? 'Survey berhasil dibuka!' : 'Survey berhasil ditutup!';
                        showAlert(response.message || msg, 'success');
                    } else {
                        showAlert('Perubahan status gagal.', 'error');
                    }
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal mengubah status.';
                    showAlert(msg, 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    }

    /**
     * Reset modals
     */
    function resetModals() {
        $('#modalTambah').on('hidden.bs.modal', function() {
            $('#formTambah')[0].reset();
            $('#formTambah .is-invalid').removeClass('is-invalid');
        });
        
        $('#modalEdit').on('hidden.bs.modal', function() {
            $('#formEdit .is-invalid').removeClass('is-invalid');
        });
    }

    /**
     * Initialize
     */
    function init() {
        initDataTable();
        handleFormTambah();
        handleEdit();
        handleFormEdit();
        handleDelete();
        handleToggleStatus();
        resetModals();
    }

    $(document).ready(init);

})();