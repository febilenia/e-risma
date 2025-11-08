
(function() {
    'use strict';

    /**
     * Show alert notification
     * @param {string} message - Alert message
     * @param {string} type - Alert type: success|error|warning|info
     * @param {number} duration - Auto-hide duration in ms (default: 5000)
     */
    function showAlert(message, type = 'success', duration = 5000) {
        const alertTypes = {
            'success': { class: 'alert-success', icon: 'check-circle' },
            'error': { class: 'alert-danger', icon: 'exclamation-triangle' },
            'warning': { class: 'alert-warning', icon: 'exclamation-circle' },
            'info': { class: 'alert-info', icon: 'info-circle' }
        };

        const config = alertTypes[type] || alertTypes['info'];
        
        const alertHtml = `
            <div class="alert ${config.class} alert-dismissible fade show" role="alert">
                <i class="fas fa-${config.icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;

        const container = document.getElementById('alertContainer');
        if (container) {
            container.innerHTML = alertHtml;
            
            // Auto hide after duration
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, duration);
        }
    }

    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @returns {boolean} - User confirmation result
     */
    function showConfirm(message) {
        return confirm(message);
    }

    // Export to global scope
    window.showAlert = showAlert;
    window.showConfirm = showConfirm;

})();