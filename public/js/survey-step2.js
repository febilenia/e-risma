// State management
let isSubmitting = false;
let autoNextTimer = null;
let saveUrl = null;

// ============================================
// AUTOSAVE FUNCTIONALITY
// ============================================

async function autosave(val, url) {
    if (!url || isSubmitting) return;
    
    const csrf = getCsrfToken();
    const kid = document.querySelector('input[name="kuesioner_id"]')?.value;
    
    if (!csrf || !kid) return;
    
    try {
        await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                kuesioner_id: kid, 
                jawaban: val 
            })
        });
    } catch (error) {
        // Silent fail for autosave
    }
}

// ============================================
// AUTO NEXT (RADIO BUTTON)
// ============================================

async function handleAutoNext(radio) {
    if (autoNextTimer) clearTimeout(autoNextTimer);
    if (isSubmitting) return;
    
    autoNextTimer = setTimeout(async () => {
        const form = radio.closest('form');
        if (!form || isSubmitting) return;
        
        showLoading('next');
        
        if (saveUrl) {
            autosave(radio.value, saveUrl).catch(() => {});
        }
        
        const submitBtn = form.querySelector('button[type="submit"]') || 
                         form.querySelector('#hidden-submit');
        submitBtn?.click();
    }, 200);
}

// ============================================
// FORM SUBMIT HANDLER
// ============================================

async function handleFormSubmit(e) {
    e.preventDefault();
    if (isSubmitting) return;
    
    isSubmitting = true;

    const form = e.currentTarget;
    const btn = form.querySelector('button[type="submit"]');
    
    if (!btn) {
        isSubmitting = false;
        hideLoading();
        return;
    }

    const btnText = btn.querySelector('.btn-text');
    const btnIcon = btn.querySelector('.btn-icon');
    const originalText = btnText?.textContent.trim();
    const originalIcon = btnIcon?.innerHTML;
    const isLastQuestion = btnText?.textContent.trim() === 'Selesai';
    
    showLoading(isLastQuestion ? 'last' : 'next');
    
    if (btnText) btnText.textContent = 'Memproses...';
    if (btnIcon) {
        btnIcon.innerHTML = `
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
    }
    btn.disabled = true;

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: new FormData(form)
        });

        // Check if survey already completed
        if (res.status === 410) {
            const err = await res.json().catch(() => ({}));
            if (err.completed) {
                showSurveyCompletedModal();
                isSubmitting = false;
                return;
            }
        }

        // Handle validation errors
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            
            if (res.status === 422 && err.errors) {
                if (err.errors['cf-turnstile-response']) {
                    alert('Verifikasi keamanan gagal! Silakan coba lagi.');
                } else {
                    alert('Terjadi kesalahan: ' + Object.values(err.errors).flat().join('\n'));
                }
            } else {
                throw new Error(`HTTP ${res.status}`);
            }
            
            hideLoading();
            if (btnText) btnText.textContent = originalText;
            if (btnIcon) btnIcon.innerHTML = originalIcon;
            btn.disabled = false;
            isSubmitting = false;
            return;
        }

        const data = await res.json();
        
        // Redirect if needed
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        
        if (!data.html) throw new Error('No HTML');

        // Update content
        const root = document.getElementById('step2-root');
        if (!root) {
            window.location.reload();
            return;
        }

        const tmp = document.createElement('div');
        tmp.innerHTML = data.html.trim();
        root.innerHTML = (tmp.querySelector('#step2-root') || tmp).innerHTML;
        
        hideLoading();
        isSubmitting = false;
        initializeNewContent();
        window.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (error) {
        hideLoading();
        alert('Terjadi kesalahan: ' + error.message);
        
        if (btnText) btnText.textContent = originalText;
        if (btnIcon) btnIcon.innerHTML = originalIcon;
        btn.disabled = false;
        isSubmitting = false;
    }
}

// ============================================
// PREVIOUS BUTTON HANDLER
// ============================================

function initPrevButton() {
    const prevBtn = document.getElementById('prev-button');
    if (!prevBtn) return;
    
    const newBtn = prevBtn.cloneNode(true);
    prevBtn.replaceWith(newBtn);
    
    newBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (isSubmitting) return;
        
        const url = newBtn.getAttribute('data-prev-url');
        if (!url) return;
        
        showLoading('prev');
        
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            
            if (res.status === 410) {
                const err = await res.json().catch(() => ({}));
                if (err.completed) {
                    showSurveyCompletedModal();
                    return;
                }
            }
            
            if (!res.ok) throw new Error("HTTP " + res.status);
            
            const data = await res.json();
            
            if (data.success && data.html) {
                const root = document.getElementById('step2-root');
                const tmp = document.createElement('div');
                tmp.innerHTML = data.html.trim();
                root.innerHTML = (tmp.querySelector('#step2-root') || tmp).innerHTML;
                
                hideLoading();
                initializeNewContent();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                hideLoading();
            }
        } catch (err) {
            hideLoading();
            showError('Gagal kembali ke pertanyaan sebelumnya');
        }
    });
}

// ============================================
// TURNSTILE WIDGET
// ============================================

function reRenderTurnstile() {
    const container = document.getElementById('turnstile-container');
    if (!container) return;
    
    if (typeof turnstile === 'undefined') {
        let attempts = 0;
        const waitInterval = setInterval(() => {
            attempts++;
            if (typeof turnstile !== 'undefined') {
                clearInterval(waitInterval);
                renderTurnstileWidget(container);
            } else if (attempts > 20) {
                clearInterval(waitInterval);
            }
        }, 300);
    } else {
        renderTurnstileWidget(container);
    }
}

function renderTurnstileWidget(container) {
    if (!container) return;
    
    container.innerHTML = '';
    
    try {
        const siteKey = container.getAttribute('data-sitekey');
        if (!siteKey) return;
        
        turnstile.render(container, {
            sitekey: siteKey,
            theme: 'light',
            size: 'normal',
            appearance: 'always',
            language: 'id',
            action: 'verify',
            cData: 'survey-step-final',
            'refresh-expired': 'manual',
            retry: 'never',
            
            callback: function(token) {
                // Verification successful
            },
            
            'error-callback': function(error) {
                alert('Verifikasi gagal. Silakan refresh halaman dan coba lagi.');
            },
            
            'expired-callback': function() {
                // Token expired
            },
            
            'timeout-callback': function() {
                // Timeout
            }
        });
    } catch (error) {
        // Render error
    }
}

// ============================================
// INITIALIZE NEW CONTENT AFTER AJAX
// ============================================

function initializeNewContent() {
    // Reset state
    isSubmitting = false;
    
    // Get save URL
    saveUrl = document.querySelector('[data-autosave-url]')?.getAttribute('data-autosave-url');
    
    const progressBar = document.querySelector('.progress-bar-dynamic');
    if (progressBar) {
        const progress = progressBar.getAttribute('data-progress');
        progressBar.style.width = progress + '%';
    }
    
    // Form submit handler
    const form = document.getElementById('question-form');
    if (form) {
        form.removeEventListener('submit', handleFormSubmit);
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Previous button
    initPrevButton();
    
    // Radio buttons with autosave
    if (saveUrl) {
        document.querySelectorAll('.radio-input').forEach(r => {
            const newRadio = r.cloneNode(true);
            r.replaceWith(newRadio);
            
            newRadio.addEventListener('click', function() {
                if (isSubmitting) return;
                
                autosave(this.value, saveUrl);
                
                if (this.dataset.autoNext === 'true') {
                    handleAutoNext(this);
                }
            });
        });
        
        // Textarea with autosave
        const ta = document.querySelector('.textarea-input');
        if (ta) {
            let timer = null;
            
            ta.oninput = null;
            ta.onblur = null;
            
            ta.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => autosave(ta.value, saveUrl), 400);
            });
            
            ta.addEventListener('blur', () => autosave(ta.value, saveUrl));
        }
    }
    
    // Re-render Turnstile if exists
    reRenderTurnstile();
}

// ============================================
// INITIALIZE ON DOM READY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Only run on step2
    if (document.getElementById('step2-root')) {
        initializeNewContent();
    }
});