/**
 * Get CSRF Token from meta tag
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Show loading overlay
 */
function showLoading(textType = 'next') {
    const overlay = document.getElementById('loading-overlay');
    if (!overlay) return;
    
    const text = overlay.querySelector('#loading-text');
    if (text) {
        const messages = {
            'prev': 'Memuat pertanyaan sebelumnya...',
            'last': 'Menyimpan jawaban Anda...',
            'next': 'Memuat pertanyaan berikutnya...'
        };
        text.textContent = messages[textType] || messages.next;
    }
    overlay.classList.remove('hidden');
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('hidden');
}

/**
 * Show error alert
 */
function showError(msg) {
    document.querySelectorAll('.alert-error').forEach(a => a.remove());
    
    const alert = document.createElement('div');
    alert.className = 'alert-error bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-4';
    alert.innerHTML = `
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>${msg}</span>
        </div>
    `;
    
    const container = document.querySelector('.slide-in');
    if (container) {
        container.insertBefore(alert, container.firstChild);
        setTimeout(() => alert.remove(), 8000);
    }
}

/**
 * Show survey completed modal
 */
function showSurveyCompletedModal() {
    hideLoading();
    
    document.querySelector('.survey-completed-modal')?.remove();
    
    const modal = document.createElement('div');
    modal.className = 'survey-completed-modal fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-2xl max-w-md w-full animate-scale-in">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Survey Telah Selesai</h3>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Terima kasih! Survey Anda telah berhasil tersimpan. 
                    Silakan <strong>refresh halaman</strong> untuk memulai survey baru.
                </p>
                <button onclick="window.location.reload()" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200 hover:shadow-lg">
                    Refresh Halaman
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ============================================
// BUTTON ANIMATIONS (survey.blade.php)
// ============================================

function initButtonAnimations() {
    const buttons = document.querySelectorAll('button, .btn, a[class*="bg-"]');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'all 0.2s ease';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });
}

function adjustContentHeight() {
    const header = document.querySelector('.flex-shrink-0');
    const content = document.querySelector('.survey-content');
    
    if (header && content) {
        const headerHeight = header.offsetHeight;
        const availableHeight = window.innerHeight - headerHeight - 32;
        content.style.maxHeight = availableHeight + 'px';
    }
}

// ============================================
// STEP 1 FORM VALIDATION (step1.blade.php)
// ============================================

function initStep1Validation() {
    const usiaInput = document.getElementById('usia');
    const hpInput = document.getElementById('no_hp');
    const form = document.querySelector('form');

    // Digits only helper
    function digitsOnly(e) {
        const cleaned = e.target.value.replace(/[^0-9]/g, '');
        if (e.target.value !== cleaned) e.target.value = cleaned;
    }

    // Usia validation
    if (usiaInput) {
        usiaInput.addEventListener('input', digitsOnly);
        usiaInput.addEventListener('blur', function() {
            const age = parseInt(this.value);
            if (age && (age < 17 || age > 99)) {
                this.setCustomValidity('Usia harus antara 17-99 tahun');
                this.style.borderColor = '#ef4444';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });
    }

    // No HP validation
    if (hpInput) {
        hpInput.addEventListener('input', digitsOnly);
        hpInput.addEventListener('blur', function() {
            const phone = this.value || '';
            const ok = /^08[0-9]{8,11}$/.test(phone);
            this.setCustomValidity(ok ? '' : 'Nomor HP harus format 08xxxxxxxxxx (10-13 digit)');
            this.style.borderColor = ok ? '' : '#ef4444';
        });
    }

    // Form submit handler
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (!submitBtn) return;
            
            const btnText = submitBtn.querySelector('.btn-text');
            const btnIcon = submitBtn.querySelector('.btn-icon');
            const originalText = btnText?.textContent;
            const originalIcon = btnIcon?.innerHTML;

            if (btnText) btnText.textContent = 'Memproses...';
            if (btnIcon) {
                btnIcon.innerHTML = `
                    <svg class="animate-spin w-3 h-3 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            }
            submitBtn.disabled = true;

            setTimeout(() => {
                if (submitBtn.disabled) {
                    if (btnText) btnText.textContent = originalText;
                    if (btnIcon) btnIcon.innerHTML = originalIcon;
                    submitBtn.disabled = false;
                }
            }, 3000);
        });
    }

    // Input field validation styling
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#10b981';
                this.style.backgroundColor = '#f0fdf4';
            } else {
                this.style.borderColor = '#ef4444';
                this.style.backgroundColor = '#fef2f2';
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#d1d5db';
                this.style.backgroundColor = '#ffffff';
            }
        });
    });
}

// ============================================
// INITIALIZE ON DOM READY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Survey main page animations
    initButtonAnimations();
    adjustContentHeight();
    window.addEventListener('resize', adjustContentHeight);
    
    // Step 1 validation (if on step1 page)
    if (document.getElementById('usia') || document.getElementById('no_hp')) {
        initStep1Validation();
    }
});