// public/js/components.js
(function () {
    "use strict";

    // ----------------------------
    // Sidebar Accordion
    // ----------------------------
    function initSidebarAccordion() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        sidebar.addEventListener('click', function (e) {
            const toggle = e.target.closest('.dropdown-toggle');
            if (!toggle || !sidebar.contains(toggle)) return;

            e.preventDefault();

            // Cari panel tujuan
            const targetId = toggle.getAttribute('data-target');
            let content = targetId ? document.getElementById(targetId) : null;

            if (!content) {
                const parent = toggle.parentElement;
                if (parent) {
                    const next = parent.querySelector('.dropdown-content');
                    if (next) content = next;
                }
            }
            if (!content) return;

            // Tutup panel lain
            sidebar.querySelectorAll('.dropdown-content.show').forEach(el => {
                if (el !== content) el.classList.remove('show');
            });

            sidebar.querySelectorAll('.dropdown-toggle.active-parent').forEach(el => {
                if (el !== toggle) el.classList.remove('active-parent');
            });

            // Paksa panel yg diklik tetap terbuka
            content.classList.add('show');
            toggle.classList.add('active-parent');
        }, { passive: false });

        // listener kosong ini cuma dipertahankan biar tidak break kalau nanti kamu pakai turbolinks/turbo/livewire/inertia
        window.addEventListener('turbolinks:load', () => {});
        window.addEventListener('turbo:load', () => {});
        window.addEventListener('livewire:load', () => {});
        window.addEventListener('inertia:load', () => {});
    }

    // ----------------------------
    // Topbar: dropdown user + klik luar + tombol sidebar mobile
    // ----------------------------
    function initTopbarInteractions() {
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');

        if (userMenuButton && userMenu) {
            // toggle buka/tutup dropdown user
            userMenuButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const isHidden = userMenu.classList.contains('hidden');

                if (isHidden) {
                    // buka dropdown
                    userMenu.classList.remove('hidden');
                    setTimeout(() => {
                        userMenu.classList.remove('opacity-0', 'scale-95');
                        userMenu.classList.add('show', 'opacity-100', 'scale-100');
                    }, 10);
                } else {
                    // tutup dropdown
                    userMenu.classList.remove('show', 'opacity-100', 'scale-100');
                    userMenu.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        userMenu.classList.add('hidden');
                    }, 300);
                }
            });

            // klik di luar dropdown -> tutup
            document.addEventListener('click', function (e) {
                if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                    if (!userMenu.classList.contains('hidden')) {
                        userMenu.classList.remove('show', 'opacity-100', 'scale-100');
                        userMenu.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            userMenu.classList.add('hidden');
                        }, 300);
                    }
                }
            });
        }

        // tombol hamburger sidebar (mobile)
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                // default sidebar kamu ada class "hidden md:block ..."
                // jadi di mobile dia hidden.
                // kita ubah jadi:
                // - kalau masih hidden -> buka (hapus hidden, tambah show)
                // - kalau sudah terbuka -> tutup (hapus show, tambah hidden)

                if (sidebar.classList.contains('hidden')) {
                    sidebar.classList.remove('hidden');
                    sidebar.classList.add('show');
                } else {
                    sidebar.classList.remove('show');
                    sidebar.classList.add('hidden');
                }
            });
        }
    }

    // ----------------------------
    // Set tanggal indonesia di elemen #tanggal-hari
    // ----------------------------
    function initTanggalHariID() {
        const el = document.getElementById('tanggal-hari');
        if (!el) return;

        const hari = [
            'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'
        ];
        const bulan = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];

        const t = new Date();
        const tampilTanggal = `${hari[t.getDay()]}, ${t.getDate()} ${bulan[t.getMonth()]} ${t.getFullYear()}`;

        el.innerText = tampilTanggal;
    }

    // ----------------------------
    // Init all
    // ----------------------------
    function initAll() {
        initSidebarAccordion();
        initTopbarInteractions();
        initTanggalHariID();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAll();
        }, { once: true });
    } else {
        initAll();
    }

})();
