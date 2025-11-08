
(function() {
    'use strict';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let table;
    let imageHandlers = {};

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        const columns = [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'pertanyaan' },
            { data: 'gambar_preview', orderable: false, searchable: false, className: 'text-center' },
            { data: 'tipe', className: 'text-center' },
            { data: 'kategori', className: 'text-center' },
            { data: 'persepsi', className: 'text-center' },
            { data: 'skala_label', className: 'text-center' },
            { data: 'is_mandatory', className: 'text-center' },
            { data: 'urutan', className: 'text-center' },
            { data: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ];

        const options = window.DataTablesConfig.getDefaultOptions({
            ajax: {
                url: window.kuesionerRoutes.data,
                error: window.DataTablesConfig.handleAjaxError
            },
            columns: columns,
            order: [[8, 'asc']] // Sort by urutan
        });

        table = $('#kuesioner-table').DataTable(options);
    }

    /**
     * Setup image upload handlers
     */
    function setupImageHandlers() {
        // Tambah modal
        imageHandlers.tambah = window.FormValidation.setupImageUpload({
            containerId: 'uploadContainer',
            inputId: 'gambar',
            previewId: 'imagePreview',
            removeButtonId: 'removeImage'
        });

        // Edit modal
        imageHandlers.edit = window.FormValidation.setupImageUpload({
            containerId: 'uploadContainerEdit',
            inputId: 'gambarEdit',
            previewId: 'imagePreviewEdit',
            removeButtonId: 'removeImageEdit'
        });
    }

    /**
     * Handle field visibility based on tipe
     */
    function handleFieldVisibility() {
        function toggleFields(modal) {
            const tipeSelect = modal.find('select[name="tipe"]');
            const tipe = tipeSelect.val();
            const kategoriGroup = modal.find('[id*="kategoriGroup"]');
            const persepsiGroup = modal.find('[id*="persepsiGroup"]');
            const skalaTypeGroup = modal.find('[id*="skalaTypeGroup"]');
            
            if (tipe === 'radio') {
                kategoriGroup.slideDown().find('select').prop('required', false);
                persepsiGroup.slideDown().find('select').prop('required', true);
                skalaTypeGroup.slideDown().find('select').prop('required', true);
            } else {
                kategoriGroup.slideUp().find('select').val('').prop('required', false);
                persepsiGroup.slideUp().find('select').val('').prop('required', false);
                skalaTypeGroup.slideUp().find('select').val('').prop('required', false);
            }
        }

        $('#modalTambah select[name="tipe"]').on('change', function() {
            toggleFields($('#modalTambah'));
        });

        $('#modalEdit select[name="tipe"]').on('change', function() {
            toggleFields($('#modalEdit'));
        });
    }

    /**
     * Check urutan availability
     */
    function checkUrutanAvailability(input, excludeId = null) {
        const urutan = input.val();
        const feedbackDiv = input.next('.invalid-feedback');
        
        if (!urutan || urutan < 1) {
            input.removeClass('is-invalid is-valid');
            feedbackDiv.hide();
            return Promise.resolve(true);
        }
        
        return $.ajax({
            url: '/dashboard/kuesioner/check-urutan',
            method: 'GET',
            data: {
                urutan: urutan,
                exclude_id: excludeId
            }
        }).then(function(response) {
            if (response.exists) {
                input.removeClass('is-valid').addClass('is-invalid');
                feedbackDiv.text('Urutan sudah digunakan. Silakan pilih urutan yang berbeda.').show();
                return false;
            } else {
                input.removeClass('is-invalid').addClass('is-valid');
                feedbackDiv.hide();
                return true;
            }
        }).catch(function() {
            input.removeClass('is-invalid is-valid');
            feedbackDiv.hide();
            return true;
        });
    }

    /**
     * Handle form tambah
     */
    function handleFormTambah() {
        // Real-time urutan validation
        $('#modalTambah input[name="urutan"]').on('blur change', function() {
            checkUrutanAvailability($(this));
        });

        $('#formTambah').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const urutanInput = $(this).find('input[name="urutan"]');
            
            checkUrutanAvailability(urutanInput).then(function(isValid) {
                if (!isValid) {
                    showAlert('Urutan sudah digunakan! Silakan pilih urutan yang berbeda.', 'error');
                    urutanInput.focus();
                    return;
                }
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
                
                const formData = new FormData($('#formTambah')[0]);
                
                $.ajax({
                    url: window.kuesionerRoutes.store,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#modalTambah').modal('hide');
                        $('#formTambah')[0].reset();
                        if (imageHandlers.tambah) imageHandlers.tambah.removeImage();
                        
                        table.ajax.reload(function() {
                            showAlert(response.message || 'Data berhasil ditambahkan!', 'success');
                        }, false);
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan';
                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        showAlert(message, 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    }

    /**
     * Handle edit
     */
    function handleEdit() {
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $.get(`${window.kuesionerRoutes.base}/${id}`)
                .done(function(response) {
                    const data = response.data;
                    
                    $('#modalEdit input[name="id"]').val(data.id);
                    $('#modalEdit textarea[name="pertanyaan"]').val(data.pertanyaan);
                    $('#modalEdit select[name="kategori_id"]').val(data.kategori_id || '');
                    $('#modalEdit select[name="tipe"]').val(data.tipe);
                    $('#modalEdit select[name="persepsi"]').val(data.persepsi || '');
                    $('#modalEdit select[name="is_mandatory"]').val(data.is_mandatory ? 1 : 0);
                    $('#modalEdit input[name="urutan"]').val(data.urutan);
                    $('#modalEdit select[name="skala_type"]').val(data.skala_type || 'kualitas');
                    
                    // Handle existing image
                    const editContainer = document.getElementById('uploadContainerEdit');
                    const editPreview = document.getElementById('imagePreviewEdit');
                    if (data.gambar) {
                        editPreview.src = `/storage/${data.gambar}`;
                        editContainer.querySelector('.upload-placeholder')?.classList.add('d-none');
                        editContainer.querySelector('.image-preview-container')?.classList.remove('d-none');
                    } else if (imageHandlers.edit) {
                        imageHandlers.edit.removeImage();
                    }
                    
                    $('#modalEdit select[name="tipe"]').trigger('change');
                    $('#modalEdit').modal('show');
                })
                .fail(function() {
                    showAlert('Gagal memuat data untuk diedit', 'error');
                });
        });
    }

    /**
     * Handle form edit
     */
    function handleFormEdit() {
        // Real-time urutan validation
        $('#modalEdit input[name="urutan"]').on('blur change', function() {
            const excludeId = $('#modalEdit input[name="id"]').val();
            checkUrutanAvailability($(this), excludeId);
        });

        $('#formEdit').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            const id = $('#modalEdit input[name="id"]').val();
            const urutanInput = $(this).find('input[name="urutan"]');
            
            checkUrutanAvailability(urutanInput, id).then(function(isValid) {
                if (!isValid) {
                    showAlert('Urutan sudah digunakan! Silakan pilih urutan yang berbeda.', 'error');
                    urutanInput.focus();
                    return;
                }
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...');
                
                const formData = new FormData($('#formEdit')[0]);
                
                $.ajax({
                    url: `${window.kuesionerRoutes.base}/${id}`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        $('#modalEdit').modal('hide');
                        table.ajax.reload(function() {
                            showAlert(response.message || 'Data berhasil diperbarui!', 'success');
                        }, false);
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan';
                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        showAlert(message, 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    }

    /**
     * Handle delete
     */
    function handleDelete() {
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const pertanyaan = $(this).data('pertanyaan');
            
            if (confirm(`Apakah Anda yakin ingin menghapus pertanyaan: "${pertanyaan}"?`)) {
                $.ajax({
                    url: `${window.kuesionerRoutes.base}/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        table.ajax.reload(function() {
                            showAlert(response.message || 'Data berhasil dihapus!', 'success');
                        }, false);
                    },
                    error: function() {
                        showAlert('Gagal menghapus data', 'error');
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
            window.FormValidation.clearValidation('#formTambah');
            $('#kategoriGroup, #persepsiGroup, #skalaTypeGroup').hide();
            if (imageHandlers.tambah) imageHandlers.tambah.removeImage();
        });

        $('#modalEdit').on('hidden.bs.modal', function() {
            window.FormValidation.clearValidation('#formEdit');
            $('#kategoriGroupEdit, #persepsiGroupEdit, #skalaTypeGroupEdit').hide();
        });
    }

    /**
     * Initialize
     */
    function init() {
        initDataTable();
        setupImageHandlers();
        handleFieldVisibility();
        handleFormTambah();
        handleEdit();
        handleFormEdit();
        handleDelete();
        resetModals();
    }

    $(document).ready(init);

})();