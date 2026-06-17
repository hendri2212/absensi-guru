<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // =====================
        // JAM DIGITAL
        // =====================
        function updateJam() {
            const jamElement = document.getElementById('jam');
            if (jamElement) {
                const now = new Date();
                jamElement.innerText = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        }
        if (document.getElementById('jam')) {
            setInterval(updateJam, 1000);
            updateJam();
        }

        // =====================
        // TOGGLE PASSWORD
        // =====================
        const toggleBtn = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#passwordField');
        if (toggleBtn && passwordField) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                const eyeIcon = document.querySelector('#eyeIcon');
                if (eyeIcon) {
                    eyeIcon.classList.toggle('bi-eye');
                    eyeIcon.classList.toggle('bi-eye-slash');
                }
            });
        }

        // =====================
        // CHART DASHBOARD GURU (Doughnut)
        // =====================
        const absensiChartCanvas = document.getElementById('absensiChart');
        if (absensiChartCanvas && absensiChartCanvas.dataset.stats) {
            try {
                const chartData = JSON.parse(absensiChartCanvas.dataset.stats);
                const total = chartData.hadir + chartData.izin + chartData.sakit + chartData.alpa;

                if (total > 0) {
                    new Chart(absensiChartCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                            datasets: [{
                                data: [chartData.hadir, chartData.izin, chartData.sakit,
                                    chartData.alpa
                                ],
                                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444'],
                                borderWidth: 0,
                                hoverOffset: 8,
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 16,
                                        usePointStyle: true,
                                        boxWidth: 10,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleColor: '#f1f5f9',
                                    bodyColor: '#cbd5e1',
                                    padding: 8,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const total = context.dataset.data.reduce((a, b) => a +
                                                b, 0);
                                            const percent = total > 0 ? Math.round((value / total) *
                                                100) : 0;
                                            return `${label}: ${value} (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '65%'
                        }
                    });
                } else {
                    absensiChartCanvas.parentElement.innerHTML =
                        '<div class="text-center py-5"><i class="bi bi-bar-chart-steps display-1 text-muted opacity-25"></i><p class="text-muted mt-2 small mb-0">Belum ada data kehadiran untuk periode ini.</p></div>';
                }
            } catch (e) {
                console.error('Chart error:', e);
            }
        }

        // =====================
        // SWEETALERT - RESTORE DATABASE
        // =====================
        const btnRestore = document.getElementById('btnRestore');
        if (btnRestore) {
            btnRestore.addEventListener('click', function() {
                Swal.fire({
                    title: 'Yakin ingin restore?',
                    text: 'Data saat ini akan tertimpa permanen dan tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Restore!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formRestore').submit();
                    }
                });
            });
        }

        // =====================
        // SWEETALERT - HAPUS GURU/SISWA
        // =====================
        document.addEventListener('click', function(event) {
            const btnHapus = event.target.closest('.btn-hapus');
            if (btnHapus) {
                event.preventDefault();
                const id = btnHapus.getAttribute('data-id');
                const nama = btnHapus.getAttribute('data-nama');
                Swal.fire({
                    title: 'Hapus Data?',
                    text: `Data "${nama}" akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('form-hapus-' + id);
                        if (form) {
                            form.submit();
                        } else {
                            // Fallback: cari form terdekat
                            const closestForm = btnHapus.closest('form');
                            if (closestForm) closestForm.submit();
                        }
                    }
                });
            }
        });

        // =====================
        // SWEETALERT - SESSION FLASH MESSAGES
        // =====================
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2500,
                showConfirmButton: false,
                toast: true,
                position: 'top-center',
                background: '#f0fdf4',
                color: '#166534'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-center',
                background: '#fef2f2',
                color: '#991b1b'
            });
        @endif
    });

    // =====================
    // SEARCH FUNCTION (Support Desktop & Mobile)
    // =====================
    function initSearch(searchInputId, targetSelector) {
        const searchInput = document.getElementById(searchInputId);
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const keyword = this.value.toLowerCase().trim();
                const targets = document.querySelectorAll(targetSelector);

                targets.forEach(target => {
                    const text = target.innerText.toLowerCase();
                    target.style.display = text.includes(keyword) ? '' : 'none';
                });
            }, 300);
        });
    }

    // Search Guru (Desktop table)
    initSearch('searchGuru', 'tbody tr');

    // Search Siswa (Admin & Guru)
    initSearch('searchSiswa', 'tbody tr');
    initSearch('searchSiswaGuru', 'tbody tr');

    // =====================
    // IMPORT SISWA - LOADING STATE
    // =====================
    const formImport = document.querySelector('#modalImportSiswa form');
    if (formImport) {
        formImport.addEventListener('submit', function(e) {
            const btn = formImport.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimport...';
            }
        });
    }

    /* =========================================
   ADMIN DASHBOARD TREND CHART
========================================= */

    document.addEventListener('DOMContentLoaded', function() {

        const trendChartCanvas = document.getElementById('trendChart');

        if (!trendChartCanvas) return;

        try {

            const labels = JSON.parse(
                trendChartCanvas.dataset.labels || '[]'
            );

            const chartData = JSON.parse(
                trendChartCanvas.dataset.data || '[]'
            );

            /* ================================
               EMPTY STATE
            ================================= */

            if (labels.length === 0 || chartData.length === 0) {

                trendChartCanvas.parentElement.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart-steps display-1 text-muted opacity-25"></i>

                    <p class="text-muted mt-3 mb-0">
                        Belum ada data untuk periode ini.
                    </p>
                </div>
            `;

                return;
            }

            /* ================================
               CHART INIT
            ================================= */

            new Chart(trendChartCanvas, {
                type: 'bar',

                data: {
                    labels: labels,

                    datasets: [{
                        label: 'Guru yang Absen',

                        data: chartData,

                        backgroundColor: 'rgba(13, 110, 253, 0.12)',

                        borderColor: 'rgba(13, 110, 253, 0.8)',

                        borderWidth: 2,

                        borderRadius: 8,

                        barPercentage: 0.65,

                        categoryPercentage: 0.8,
                    }]
                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {
                            display: false
                        },

                        tooltip: {
                            backgroundColor: '#1e293b',

                            titleColor: '#f8fafc',

                            bodyColor: '#cbd5e1',

                            padding: 10,

                            cornerRadius: 10,
                        }
                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {
                                stepSize: 1,
                                precision: 0
                            },

                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            }
                        },

                        x: {

                            grid: {
                                display: false
                            },

                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });

        } catch (error) {

            console.error('Trend chart error:', error);

            trendChartCanvas.parentElement.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-circle display-1 text-danger opacity-25"></i>

                <p class="text-danger mt-3 mb-0">
                    Gagal memuat chart.
                </p>
            </div>
        `;
        }
    });

    // =====================
    // AUTO-REFRESH CSRF TOKEN (Login Page)
    // =====================
    @if (Request::is('login'))
        // Refresh saat balik ke tab
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                refreshCsrfToken();
            }
        });

        // Refresh saat user klik/focus form (cover autofill)
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');
            if (loginForm) {
                loginForm.addEventListener('focusin', refreshCsrfToken, {
                    once: true
                });
            }

            // Refresh sekali saat halaman load
            refreshCsrfToken();
        });

        function refreshCsrfToken() {
            fetch('/csrf-token')
                .then(res => res.json())
                .then(data => {
                    document.querySelectorAll('input[name="_token"]')
                        .forEach(el => el.value = data.token);
                    document.querySelector('meta[name="csrf-token"]')
                        ?.setAttribute('content', data.token);
                })
                .catch(() => {});
        }
    @endif

    // Pertahankan tab aktif - student-detail page only
    const studentContainer = document.querySelector('[data-student-id]');
    if (studentContainer) {
        const studentId = studentContainer.dataset.studentId;
        const tabKey = 'studentDetailActiveTab_' + studentId;
        const savedTab = localStorage.getItem(tabKey);
        if (savedTab) {
            const el = document.querySelector(`[data-bs-target="${savedTab}"]`);
            if (el) bootstrap.Tab.getOrCreateInstance(el).show();
        }
        document.querySelectorAll('#detailTab [data-bs-toggle="pill"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', e => {
                localStorage.setItem(tabKey, e.target.dataset.bsTarget);
            });
        });
    }
</script>
