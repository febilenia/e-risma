
(function() {
    'use strict';

    const DataTablesConfig = {
        /**
         * Get default Indonesian language configuration
         */
        getLanguage() {
            return {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                search: "Cari:",
                processing: "Memuat data...",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            };
        },

        /**
         * Get default DataTables options
         * @param {object} customOptions - Custom options to merge with defaults
         * @returns {object} - Merged DataTables configuration
         */
        getDefaultOptions(customOptions = {}) {
            const defaults = {
                processing: true,
                serverSide: true,
                responsive: true,
                scrollX: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                language: this.getLanguage(),
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            };

            // Deep merge using jQuery
            return $.extend(true, {}, defaults, customOptions);
        },

        /**
         * Handle AJAX errors consistently
         */
        handleAjaxError(xhr, error, thrown) {
            console.error('DataTables Ajax Error:', error, thrown);
            if (typeof showAlert === 'function') {
                showAlert('Gagal memuat data. Silakan refresh halaman.', 'error');
            }
        }
    };

    // Export to global scope
    window.DataTablesConfig = DataTablesConfig;

})();