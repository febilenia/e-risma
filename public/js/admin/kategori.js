
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
            { 
                data: 'nama_kategori',
                render: function(data) {
                    return `<span class="fw-semibold">${data}</span>`;
                }
            },
            { 
                data: 'jumlah_pertanyaan', 
                orderable: false, 
                searchable: false, 
                className: 'text-center',
                render: function(data) {
                    const badgeClass = data > 0 ? 'bg-success' : 'bg-secondary';
                    return `<span class="badge ${badgeClass} fs-6">${data} Pertanyaan</span>`;
                }
            },
            { data: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.kategoriRoutes.data,
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns,
            order: [[1, 'asc']]
        });

        table = $('#kategori-table').DataTable(options);
    }

    /**
     * Handle form tambah
     */
    function handleFormTambah() {
        $('#formTambah').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            $('.is-invalid').removeClass('is-invalid');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
            
            $.ajax({
                url: window.kategoriRoutes.store,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Kategori berhasil ditambahkan!', 'success');
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal menyimpan data kategori.';
                    
                    if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.nama_kategori) {
                            $('#formTambah input[name="nama_kategori"]').addClass('is-invalid');
                            errorMessage = errors.nama_kategori[0];
                        }
                    }
                    
                    showAlert(errorMessage, 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    }

    /**
     * Handle edit
     */
    function handleEdit() {
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $('.is-invalid').removeClass('is-invalid');
            
            $.get(`${window.kategoriRoutes.base}/${id}/edit`)
                .done(function(data) {
                    $('#modalEdit input[name="id"]').val(data.id);
                    $('#modalEdit input[name="nama_kategori"]').val(data.nama_kategori);
                    $('#modalEdit').modal('show');
                })
                .fail(function() {
                    showAlert('Gagal memuat data untuk diedit.', 'error');
                });
        });
    }

    /**
     * Handle form edit
     */
    function handleFormEdit() {
        $('#formEdit').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const id = $('#formEdit input[name="id"]').val();
            
            $('.is-invalid').removeClass('is-invalid');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...');
            
            $.ajax({
                url: `${window.kategoriRoutes.base}/${id}`,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#modalEdit').modal('hide');
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Kategori berhasil diperbarui!', 'success');
                },
                error: function(xhr) {
                    let errorMessage = 'Gagal mengupdate data kategori.';
                    
                    if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.nama_kategori) {
                            $('#formEdit input[name="nama_kategori"]').addClass('is-invalid');
                            errorMessage = errors.nama_kategori[0];
                        }
                    }
                    
                    showAlert(errorMessage, 'error');
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
            const id = $(this).data('id');
            const nama = $(this).data('nama') || 'kategori ini';
            
            if (confirm(`Apakah Anda yakin ingin menghapus kategori "${nama}"?\n\nHati-hati: Jika kategori ini memiliki pertanyaan, maka tidak bisa dihapus.`)) {
                $.ajax({
                    url: `${window.kategoriRoutes.base}/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        table.ajax.reload(null, false);
                        showAlert(response.message || 'Kategori berhasil dihapus!', 'success');
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.message || 'Gagal menghapus kategori.';
                        showAlert(errorMessage, 'error');
                    }
                });
            }
        });
    }

    /**
     * Handle preview pertanyaan
     */
    function handlePreview() {
        $(document).on('click', '.btn-preview', function() {
            const kategoriId = $(this).data('id');
            const namaKategori = $(this).data('nama');

            $('#previewModalLabel').html(`<i class="fas fa-eye me-2"></i>Pertanyaan dalam Kategori: ${namaKategori}`);
            $('#listPertanyaan').html('<li class="list-group-item d-flex align-items-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat daftar pertanyaan...</li>');

            $.ajax({
                url: `${window.kategoriRoutes.base}/${kategoriId}/pertanyaan`,
                type: 'GET',
                success: function(res) {
                    if (res.success && res.data.length > 0) {
                        let html = '';
                        res.data.forEach((item, i) => {
                            html += `
                                <li class="list-group-item">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-primary me-3 mt-1">${i + 1}</span>
                                        <div class="flex-grow-1">
                                            <p class="mb-1">${escapeHtml(item.pertanyaan)}</p>
                                            <small class="text-muted">ID: ${item.id}</small>
                                        </div>
                                    </div>
                                </li>`;
                        });
                        $('#listPertanyaan').html(html);
                    } else {
                        $('#listPertanyaan').html(`
                            <li class="list-group-item text-muted text-center py-4">
                                <i class="fas fa-info-circle me-2 fs-4"></i>
                                <div class="mt-2">
                                    <strong>Belum ada pertanyaan</strong><br>
                                    <small>Kategori "${namaKategori}" belum memiliki pertanyaan.</small>
                                </div>
                            </li>`);
                    }
                },
                error: function() {
                    $('#listPertanyaan').html(`
                        <li class="list-group-item text-danger text-center py-4">
                            <i class="fas fa-exclamation-circle me-2 fs-4"></i>
                            <div class="mt-2">
                                <strong>Gagal memuat data</strong><br>
                                <small>Terjadi kesalahan saat memuat daftar pertanyaan.</small>
                            </div>
                        </li>`);
                }
            });

            $('#previewModal').modal('show');
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
        initDataTable();
        handleFormTambah();
        handleEdit();
        handleFormEdit();
        handleDelete();
        handlePreview();
        resetModals();
    }

    $(document).ready(init);

})();