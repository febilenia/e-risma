(function() {
    'use strict';

    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let table;

    /**
     * Initialize DataTable with server-side processing
     */
    function initDataTable() {
        const columns = [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'username', name: 'username' },
            { data: 'nama_opd', name: 'nama_opd' },
            { data: 'password_status', name: 'password_status', orderable: false, searchable: false, className: 'text-center' },
            { data: 'last_password_change', name: 'last_password_change', orderable: false, searchable: false, className: 'text-center' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.adminOpdRoutes.data,
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns
        });

        table = $('#admin-opd-table').DataTable(options);
    }

    /**
     * Handle form tambah submit
     */
    function handleFormTambah() {
        $('#formTambah').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
            
            $.post(window.adminOpdRoutes.store, $(this).serialize())
                .done(function(response) {
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Admin OPD berhasil ditambahkan!', 'success');
                })
                .fail(function(xhr) {
                    let errorMessage = 'Gagal menyimpan data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                    showAlert(errorMessage, 'error');
                })
                .always(function() {
                    submitBtn.prop('disabled', false).html(originalText);
                });
        });
    }

    /**
     * Handle edit button click
     */
    function handleEdit() {
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $.get(`${window.adminOpdRoutes.base}/${id}/edit`)
                .done(function(data) {
                    $('#modalEdit input[name=id]').val(data.id);
                    $('#modalEdit input[name=name]').val(data.name);
                    $('#modalEdit input[name=username]').val(data.username);
                    $('#modalEdit select[name=opd_id]').val(data.opd_id);
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
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const id = $('#formEdit input[name=id]').val();
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupdate...');
            
            $.post(`${window.adminOpdRoutes.base}/${id}`, $(this).serialize() + '&_method=PUT')
                .done(function(response) {
                    $('#modalEdit').modal('hide');
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Admin OPD berhasil diperbarui!', 'success');
                })
                .fail(function(xhr) {
                    let errorMessage = 'Gagal mengupdate data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                    showAlert(errorMessage, 'error');
                })
                .always(function() {
                    submitBtn.prop('disabled', false).html(originalText);
                });
        });
    }

    /**
     * Handle reset password button
     */
    function handleResetPassword() {
        // Show modal
        $(document).on('click', '.btn-reset-password', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#formResetPassword input[name=id]').val(id);
            $('#resetAdminName').text(name);
            $('#formResetPassword input[name=new_password]').val('');
            $('#modalResetPassword').modal('show');
        });

        // Submit form
        $('#formResetPassword').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const id = $('#formResetPassword input[name=id]').val();
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mereset...');
            
            $.post(`${window.adminOpdRoutes.base}/${id}/reset-password`, $(this).serialize())
                .done(function(response) {
                    $('#modalResetPassword').modal('hide');
                    table.ajax.reload(null, false);
                    showAlert(response.message || 'Password berhasil direset!', 'success');
                })
                .fail(function(xhr) {
                    let errorMessage = 'Gagal mereset password.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                    showAlert(errorMessage, 'error');
                })
                .always(function() {
                    submitBtn.prop('disabled', false).html(originalText);
                });
        });
    }

    /**
     * Handle delete button
     */
    function handleDelete() {
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name') || 'admin OPD ini';
            
            if (confirm(`Yakin ingin menghapus "${name}"?\n\nData yang dihapus tidak dapat dikembalikan.`)) {
                $.ajax({
                    url: `${window.adminOpdRoutes.base}/${id}`,
                    method: "DELETE",
                    success: function(response) {
                        table.ajax.reload(null, false);
                        showAlert(response.message || 'Admin OPD berhasil dihapus!', 'success');
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'Gagal menghapus data.';
                        showAlert(errorMessage, 'error');
                    }
                });
            }
        });
    }

    /**
     * Reset modals on close
     */
    function resetModals() {
        $('#modalTambah').on('hidden.bs.modal', function() {
            $('#formTambah')[0].reset();
            $('#formTambah .is-invalid').removeClass('is-invalid');
        });

        $('#modalEdit').on('hidden.bs.modal', function() {
            $('#formEdit .is-invalid').removeClass('is-invalid');
        });

        $('#modalResetPassword').on('hidden.bs.modal', function() {
            $('#formResetPassword .is-invalid').removeClass('is-invalid');
        });
    }

    /**
     * Initialize all handlers on DOM ready
     */
    function init() {
        initDataTable();
        handleFormTambah();
        handleEdit();
        handleFormEdit();
        handleResetPassword();
        handleDelete();
        resetModals();
    }

    // Auto-initialize when DOM is ready
    $(document).ready(init);

})();