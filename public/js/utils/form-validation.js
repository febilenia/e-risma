(function() {
    'use strict';

    const FormValidation = {
        /**
         * Validate image file
         */
        validateImage(file) {
            if (!file) return { valid: true };
            
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!validTypes.includes(file.type)) {
                return {
                    valid: false,
                    message: 'Format file harus JPG, PNG, GIF, atau SVG'
                };
            }

            if (file.size > maxSize) {
                return {
                    valid: false,
                    message: 'Ukuran file maksimal 2MB'
                };
            }

            return { valid: true };
        },

        /**
         * Setup image upload with drag & drop
         */
        setupImageUpload(options) {
            const {
                containerId,
                inputId,
                previewId,
                removeButtonId,
                onSelect,
                onRemove
            } = options;

            const container = document.getElementById(containerId);
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const removeBtn = document.getElementById(removeButtonId);

            if (!container || !input || !preview) return;

            // Click to upload
            container.addEventListener('click', function(e) {
                if (!e.target.closest('.image-preview-container')) {
                    input.click();
                }
            });

            // Drag & drop
            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                container.classList.add('dragover');
            });

            container.addEventListener('dragleave', (e) => {
                e.preventDefault();
                container.classList.remove('dragover');
            });

            container.addEventListener('drop', (e) => {
                e.preventDefault();
                container.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });

            // File input change
            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });

            // Remove button
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    removeImage();
                });
            }

            function handleFileSelect(file) {
                const validation = FormValidation.validateImage(file);
                
                if (!validation.valid) {
                    if (typeof showAlert === 'function') {
                        showAlert(validation.message, 'error');
                    }
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.querySelector('.upload-placeholder')?.classList.add('d-none');
                    container.querySelector('.image-preview-container')?.classList.remove('d-none');
                    
                    if (onSelect) onSelect(file);
                };
                reader.readAsDataURL(file);
            }

            function removeImage() {
                input.value = '';
                preview.src = '';
                container.querySelector('.upload-placeholder')?.classList.remove('d-none');
                container.querySelector('.image-preview-container')?.classList.add('d-none');
                
                if (onRemove) onRemove();
            }

            return { removeImage };
        },

        /**
         * Clear validation state
         */
        clearValidation(form) {
            $(form).find('.is-invalid').removeClass('is-invalid');
            $(form).find('.is-valid').removeClass('is-valid');
            $(form).find('.invalid-feedback').hide();
        }
    };

    // Export to global scope
    window.FormValidation = FormValidation;

})();