function initPasswordToggle() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        const container = input.closest('.relative');
        if (!container) return;
        
        const toggleBtn = container.querySelector('[id^="toggle"]');
        if (!toggleBtn) return;
        
        const eyeOpen = toggleBtn.querySelector('[id*="Open"]');
        const eyeOff = toggleBtn.querySelector('[id*="Off"]');
        
        if (!eyeOpen || !eyeOff) return;
        
        // Set initial state
        const isPasswordHidden = input.type === 'password';
        eyeOff.classList.toggle('hidden', !isPasswordHidden);
        eyeOpen.classList.toggle('hidden', isPasswordHidden);
        
        toggleBtn.addEventListener('click', () => {
            input.type = (input.type === 'password') ? 'text' : 'password';
            
            const isHidden = input.type === 'password';
            eyeOff.classList.toggle('hidden', !isHidden);
            eyeOpen.classList.toggle('hidden', isHidden);
            
            toggleBtn.classList.add('scale-95');
            setTimeout(() => toggleBtn.classList.remove('scale-95'), 120);
        });
    });
}

/**
 * Captcha Refresh
 * Untuk reload captcha di login form
 * ✅ FIX: Tambah loading state
 */
function initCaptchaRefresh() {
    const btn = document.getElementById('reload');
    if (!btn) return;
    
    const img = document.querySelector('.captcha-container img');
    if (!img) return;
    
    btn.addEventListener('click', () => {
        // ✅ Disable button & tampilkan loading
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.style.cursor = 'wait';
        
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `
            <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading...
        `;
        
        fetch('/captcha-refresh')
            .then(res => res.json())
            .then(data => {
                // ✅ Update gambar captcha
                img.src = data.captcha + '?' + Date.now();
                
                // ✅ Kembalikan button ke normal setelah gambar load
                img.onload = () => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.innerHTML = originalHTML;
                };
            })
            .catch(err => {
                console.error('Error refreshing captcha:', err);
                
                // ✅ Kembalikan button meski error
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.innerHTML = originalHTML;
                
                alert('Gagal refresh captcha. Silakan coba lagi.');
            });
    });
}

/**
 * Toast Success Notification
 * Tampilkan notifikasi sukses (misal setelah logout/change password)
 * ✅ FIX: Jangan tampilkan kalau message kosong
 */
function initToastNotification() {
    const root = document.getElementById('toast-sukses');
    if (!root) return;
    
    const msg = document.getElementById('toast-sukses-msg');
    const bar = document.querySelector('#toast-sukses-bar > div');
    
    if (!msg) return;
    
    // ✅ FIX: Cek apakah message ada isinya
    const messageText = msg.textContent.trim();
    if (!messageText || messageText === '') {
        return; // Jangan tampilkan toast kalau message kosong
    }
    
    // Tampilkan toast dengan animasi
    root.classList.remove('hidden');
    root.style.opacity = '0';
    root.style.transform = 'translateY(-8px)';
    
    requestAnimationFrame(() => {
        root.style.transition = 'all .25s ease';
        root.style.opacity = '1';
        root.style.transform = 'translateY(0)';
    });
    
    // Progress bar
    if (bar) {
        bar.style.background = 'rgba(250,204,21,.9)';
        bar.style.transform = 'scaleX(1)';
        bar.style.transition = 'transform 1.8s linear';
        requestAnimationFrame(() => bar.style.transform = 'scaleX(0)');
    }
    
    // Auto hide setelah 1.8 detik
    setTimeout(() => {
        root.style.opacity = '0';
        root.style.transform = 'translateY(-8px)';
        setTimeout(() => {
            root.classList.add('hidden');
            msg.textContent = ''; // ✅ Clear message setelah hide
        }, 250);
    }, 1800);
}

/**
 * Init semua fungsi pas halaman load
 */
document.addEventListener('DOMContentLoaded', function() {
    initPasswordToggle();
    initCaptchaRefresh();
    initToastNotification();
});

window.initToastNotification = initToastNotification;