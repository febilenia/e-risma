(function() {
    'use strict';
    
    let widgetId = null;
    let renderAttempts = 0;
    const maxAttempts = 20;
    
    /**
     * Render Turnstile widget on last question
     */
    function renderTurnstileWidget() {
        renderAttempts++;
        
        // Check if Turnstile library is loaded
        if (typeof turnstile === 'undefined') {
            if (renderAttempts < maxAttempts) {
                setTimeout(renderTurnstileWidget, 300);
            }
            return;
        }
        
        const container = document.getElementById('turnstile-container');
        
        // Don't render if container doesn't exist or already has content
        if (!container || container.children.length > 0) return;
        
        try {
            const siteKey = container.getAttribute('data-sitekey');
            if (!siteKey) {
                console.error('Turnstile site key not found');
                return;
            }
            
            widgetId = turnstile.render('#turnstile-container', {
                sitekey: siteKey,
                theme: 'light',
                size: 'normal',
                language: 'id',
                appearance: 'interaction-only',
                action: 'submit-survey',
                cData: 'survey-last-question',
                
                callback: function(token) {
                    // Verification successful
                },
                
                'error-callback': function(error) {
                    alert('Verifikasi gagal. Silakan refresh halaman dan coba lagi.');
                },
                
                'expired-callback': function() {
                    // Token expired - widget will auto-refresh
                },
                
                'timeout-callback': function() {
                    // Timeout occurred
                }
            });
        } catch (error) {
            console.error('Turnstile render error:', error);
        }
    }
    
    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderTurnstileWidget);
    } else {
        renderTurnstileWidget();
    }
    
})();