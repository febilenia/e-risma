

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
            { data: 'nama_opd', className: 'text-wrap' },
            { data: 'total_aplikasi', orderable: false, searchable: false, className: 'text-center' },
            { data: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.opdRoutes.index,
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns
        });

        table = $('#opd-table').DataTable(options);
    }

    /**
     * Handle form tambah submit
     */
    function handleFormTambah() {
        $('#formTambah').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $(this).find('button[type="submit"]');
            const oldText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
            
            $.post(window.opdRoutes.store, $(this).serialize())
                .done(function(response) {
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Data OPD berhasil ditambahkan!', 'success');
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'Gagal menyimpan data.';
                    showAlert(msg, 'error');
                })
                .always(function() {
                    btn.prop('disabled', false).html(oldText);
                });
        });
    }

    /**
     * Handle edit button click
     */
    function handleEdit() {
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $.get(`${window.opdRoutes.base}/${id}/edit`)
                .done(function(data) {
                    $('#modalEdit input[name=id]').val(data.id);
                    $('#modalEdit input[name=nama_opd]').val(data.nama_opd);
                    $('#modalEdit').modal('show');
                })
                .fail(function() {
                    showAlert('Gagal memuat data untuk diedit.', 'error');
                });
        });
    }

    /**
     * Handle form edit submit
     */
    function handleFormEdit() {
        $('#formEdit').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $(this).find('button[type="submit"]');
            const oldText = btn.html();
            const id = $('#formEdit input[name=id]').val();
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...');
            
            $.post(`${window.opdRoutes.base}/${id}/update`, $(this).serialize())
                .done(function(response) {
                    $('#modalEdit').modal('hide');
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Data OPD berhasil diperbarui!', 'success');
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'Gagal mengupdate data.';
                    showAlert(msg, 'error');
                })
                .always(function() {
                    btn.prop('disabled', false).html(oldText);
                });
        });
    }

    /**
     * Handle delete button
     */
    function handleDelete() {
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama') || 'OPD ini';
            
            if (confirm(`Yakin hapus "${nama}"?\nData yang dihapus tidak dapat dikembalikan.`)) {
                $.ajax({
                    url: `${window.opdRoutes.base}/${id}`,
                    method: "DELETE",
                    success: function(response) {
                        table.ajax.reload(null, false);
                        showAlert(response.message || 'Data berhasil dihapus!', 'success');
                    },
                    error: function() {
                        showAlert('Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    }

    /**
     * Reset modals
     */
    function resetModals() {
        $('#modalTambah').on('hidden.bs.modal', function() {
            $('#formTambah')[0].reset();
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
        resetModals();
    }

    $(document).ready(init);

})();