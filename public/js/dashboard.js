// Format number function
function fmt(n, decimals = 3) {
    const num = Number(n) || 0;
    const s = num.toFixed(decimals);
    const negZero = '-0.' + '0'.repeat(decimals);
    return s === negZero ? s.slice(1) : s;
}

// Sidebar offset handler
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const mainWrap = document.getElementById('mainWrap');
const sidebarToggle = document.getElementById('sidebarToggle');

function applySidebarOffset() {
    if (window.innerWidth >= 768) {
        const w = sidebar ? sidebar.getBoundingClientRect().width : 0;
        if (mainWrap) mainWrap.style.marginLeft = w + 'px';
    } else {
        if (mainWrap) mainWrap.style.marginLeft = '0px';
    }
}

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
        overlay?.classList.toggle('show');
        setTimeout(applySidebarOffset, 300);
    });
}
if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar?.classList.remove('show');
        overlay?.classList.remove('show');
        setTimeout(applySidebarOffset, 300);
    });
}

document.querySelectorAll('.dropdown-toggle').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('data-target');
        const targetEl = document.getElementById(targetId);
        document.querySelectorAll('.dropdown-content.show').forEach(dd => {
            if (dd !== targetEl) dd.classList.remove('show');
        });
        if (targetEl) targetEl.classList.toggle('show');
        const icon = this.querySelector('.dropdown-icon');
        if (icon) icon.classList.toggle('rotate-icon');
    });
});

window.addEventListener('resize', applySidebarOffset);
document.addEventListener('DOMContentLoaded', applySidebarOffset);

// Date Range Variables
let currentStartDate = null;
let currentEndDate = null;
let dateRangePicker;

function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
// Initialize Flatpickr
dateRangePicker = flatpickr("#filterDateRange", {
    mode: "range",
    dateFormat: "Y-m-d",
    maxDate: "today",
    locale: {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        },
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (selectedDates.length === 2) {
            currentStartDate = selectedDates[0].formatLocalDate().split('T')[0];
            currentEndDate = selectedDates[1].formatLocalDate().split('T')[0];
            loadDashboardAnalisis();
        } else if (selectedDates.length === 0) {
            currentStartDate = null;
            currentEndDate = null;
            loadDashboardAnalisis();
        }
    }
});

// Clear Date Filter
$('#clearDateFilter').on('click', function() {
    dateRangePicker.clear();
    currentStartDate = null;
    currentEndDate = null;
    loadDashboardAnalisis();
});

// Chart initialization
let dashKategoriChart = null, dashPerformaChart = null;

function initDashCharts(){
    const cp = document.getElementById('dashPerformaChart').getContext('2d');
    dashPerformaChart = new Chart(cp, {
        type: 'bar',
        data: { labels: [], datasets: [{ label:'NKP', data: [], backgroundColor: '#1E3A8A' }] },
        options: {
            responsive:true, maintainAspectRatio:false,
            scales:{ y:{ beginAtZero:true, min: -4, max: 4 } },
            plugins:{ legend:{ display:false }}
        }
    });
}

// Update Predikat Keseluruhan
function updatePredikatKeseluruhan(rataNkp, totalAplikasi, aplikasiPunyaData) {
    document.getElementById('predikatolahanNkp').textContent = fmt(rataNkp, 2);
    document.getElementById('jumlahAplikasiAnalisis').textContent = aplikasiPunyaData;
    
    let predikat = 'Belum Ada Data';
    let badgeClass = 'badge-belum-data';
    let insight = 'Belum ada data analisis NKP yang tersedia.';
    
    if (aplikasiPunyaData > 0 && Number.isFinite(rataNkp)) {
        // ✅ OPD name akan di-pass dari blade via window.dashboardOpdName
        const opdLabel = window.dashboardOpdName || 'Kabupaten Gresik';
        
        if (rataNkp >= 2.4) {
            predikat = 'Sangat Baik';
            badgeClass = 'badge-sangat-baik';
            insight = `Layanan digital ${opdLabel} menunjukkan performa yang sangat baik dengan NKP ${fmt(rataNkp, 2)}. Pengguna merasakan manfaat yang sangat tinggi dengan risiko minimal.`;
        } else if (rataNkp >= 1.0) {
            predikat = 'Baik';
            badgeClass = 'badge-baik';
            insight = `Layanan digital ${opdLabel} menunjukkan performa yang baik dengan NKP ${fmt(rataNkp, 2)}. Terdapat ruang untuk peningkatan lebih lanjut.`;
        } else if (rataNkp >= -0.8) {
            predikat = 'Cukup';
            badgeClass = 'badge-cukup';
            insight = `Layanan digital ${opdLabel} menunjukkan performa cukup dengan NKP ${fmt(rataNkp, 2)}. Diperlukan fokus pada peningkatan manfaat dan pengurangan risiko.`;
        } else if (rataNkp >= -2.4) {
            predikat = 'Buruk';
            badgeClass = 'badge-buruk';
            insight = `Layanan digital ${opdLabel} memerlukan perbaikan dengan NKP ${fmt(rataNkp, 2)}. Pengguna masih merasakan risiko yang tinggi dibanding manfaat.`;
        } else {
            predikat = 'Sangat Buruk';
            badgeClass = 'badge-sangat-buruk';
            insight = `Layanan digital ${opdLabel} memerlukan perbaikan menyeluruh dengan NKP ${fmt(rataNkp, 2)}. Diperlukan evaluasi dan tindakan perbaikan segera.`;
        }
    }
    
    const predikatBadge = document.getElementById('predikatBadge');
    predikatBadge.className = `badge-predikat ${badgeClass}`;
    predikatBadge.textContent = predikat;
    
    document.getElementById('insightText').textContent = insight;
}

// Load Dashboard Analisis
function loadDashboardAnalisis(){
    $.ajax({
        url: "/dashboard/analisis/summary",
        method: 'GET',
        dataType: 'json',
        data: {
            start_date: currentStartDate,
            end_date: currentEndDate
        },
        success: function(json){
            const st = json.statistics || {};

            const rataNkp       = Number((st.rata_nkp ?? st.rata2_nkp) ?? 0);
            const totalAplikasi = Number(st.total_aplikasi ?? 0);
            const totalResponden= Number(st.total_responden ?? 0);
            const belumAdaData  = Number(st.belum_ada_data ?? 0);
            const aplikasiPunyaData = Math.max(0, totalAplikasi - belumAdaData);

            document.getElementById('anaTotalAplikasi').textContent = totalAplikasi;
            document.getElementById('anaTotalResponden').textContent= totalResponden;

            // ✅ Update NKP Tertinggi dan Terendah hanya untuk Superadmin
            if (window.isSuperadmin) {
                const nkpTertinggi  = Number(st.nkp_tertinggi ?? 0);
                const nkpTerendah   = Number(st.nkp_terendah ?? 0);
                const predikatTertinggi = st.predikat_tertinggi ?? '-';
                const predikatTerendah  = st.predikat_terendah ?? '-';
                const namaAplikasiTertinggi = st.nama_aplikasi_tertinggi ?? '-';
                const namaOpdTertinggi = st.nama_opd_tertinggi ?? '-';
                const namaAplikasiTerendah = st.nama_aplikasi_terendah ?? '-';
                const namaOpdTerendah = st.nama_opd_terendah ?? '-';

                document.getElementById('anaNkpTertinggi').textContent  = fmt(nkpTertinggi, 2);
                document.getElementById('anaNkpTerendah').textContent   = fmt(nkpTerendah, 2);
                document.getElementById('namaAplikasiTertinggi').textContent = namaAplikasiTertinggi;
                document.getElementById('namaOpdTertinggi').textContent = namaOpdTertinggi;
                document.getElementById('predikatTertinggi').textContent = predikatTertinggi;
                document.getElementById('namaAplikasiTerendah').textContent = namaAplikasiTerendah;
                document.getElementById('namaOpdTerendah').textContent = namaOpdTerendah;
                document.getElementById('predikatTerendah').textContent  = predikatTerendah;

                const predikatTertinggiEl = document.getElementById('predikatTertinggi');
                const predikatTerendahEl = document.getElementById('predikatTerendah');
                
                predikatTertinggiEl.className = 'font-semibold ' + getPredikatColor(predikatTertinggi);
                predikatTerendahEl.className = 'font-semibold ' + getPredikatColor(predikatTerendah);
            }

            updatePredikatKeseluruhan(rataNkp, totalAplikasi, aplikasiPunyaData);

            if (dashPerformaChart) {
                const rows = (json.data || []).slice(0, 10);
                dashPerformaChart.data.labels = rows.map(r => r.nama_aplikasi);
                dashPerformaChart.data.datasets[0].data = rows.map(r => parseFloat(r.nkp ?? 0));
                dashPerformaChart.update();
            }
        },
        error: function(xhr){
            console.warn('Gagal mengambil analisis untuk dashboard.', xhr.status, xhr.responseText);
            document.getElementById('anaTotalAplikasi').textContent = '0';
            document.getElementById('anaNkpTertinggi').textContent  = '0.00';
            document.getElementById('anaNkpTerendah').textContent   = '0.00';
            document.getElementById('predikatTertinggi').textContent = '-';
            document.getElementById('predikatTerendah').textContent  = '-';
            document.getElementById('anaTotalResponden').textContent= '0';
            updatePredikatKeseluruhan(0, 0, 0);
        }
    });
}

// Get Predikat Color
function getPredikatColor(predikat) {
    switch(predikat) {
        case 'Sangat Baik':
            return 'text-blue-600';
        case 'Baik':
            return '';
        case 'Cukup':
            return 'text-yellow-600';
        case 'Buruk':
            return 'text-orange-600';
        case 'Sangat Buruk':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
}

// DOMContentLoaded
document.addEventListener('DOMContentLoaded', function(){
    initDashCharts();
    loadDashboardAnalisis();
});