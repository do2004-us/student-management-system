document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('sms_theme') || 'light';
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const profileToggle = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const notificationToggle = document.querySelector('[data-notification-toggle]');
    const notificationMenu = document.querySelector('[data-notification-menu]');
    const mobileToggle = document.querySelector('[data-mobile-nav]');
    const sidebar = document.querySelector('.sidebar');

    root.setAttribute('data-theme', savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', nextTheme);
            localStorage.setItem('sms_theme', nextTheme);
            showToast(`${nextTheme === 'dark' ? 'Dark' : 'Light'} mode enabled`);
        });
    }

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', () => {
            profileMenu.classList.toggle('open');
            notificationMenu?.classList.remove('open');
        });
    }

    if (notificationToggle && notificationMenu) {
        notificationToggle.addEventListener('click', () => {
            notificationMenu.classList.toggle('open');
            profileMenu?.classList.remove('open');
        });
    }

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-profile-wrap]')) {
            profileMenu?.classList.remove('open');
        }

        if (!event.target.closest('[data-notification-wrap]')) {
            notificationMenu?.classList.remove('open');
        }
    });

    buildAdminCharts();
    animateProgressBars();
});

function showToast(message) {
    let toast = document.querySelector('.toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add('show');

    window.setTimeout(() => {
        toast.classList.remove('show');
    }, 2600);
}

function animateProgressBars() {
    document.querySelectorAll('[data-progress]').forEach((bar) => {
        const value = bar.getAttribute('data-progress') || '0';
        requestAnimationFrame(() => {
            bar.style.width = `${value}%`;
        });
    });
}

function buildAdminCharts() {
    if (typeof Chart === 'undefined') {
        return;
    }

    const attendanceCanvas = document.getElementById('attendanceChart');
    const feeCanvas = document.getElementById('feeChart');
    const performanceCanvas = document.getElementById('performanceChart');

    if (attendanceCanvas) {
        const labels = JSON.parse(attendanceCanvas.dataset.labels || '[]');
        const values = JSON.parse(attendanceCanvas.dataset.values || '[]');

        new Chart(attendanceCanvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
                cutout: '68%',
            },
        });
    }

    if (feeCanvas) {
        const labels = JSON.parse(feeCanvas.dataset.labels || '[]');
        const values = JSON.parse(feeCanvas.dataset.values || '[]');

        new Chart(feeCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Fees',
                    data: values,
                    backgroundColor: '#2563eb',
                    borderRadius: 10,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    if (performanceCanvas) {
        const labels = JSON.parse(performanceCanvas.dataset.labels || '[]');
        const values = JSON.parse(performanceCanvas.dataset.values || '[]');

        new Chart(performanceCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Average Score',
                    data: values,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.14)',
                    fill: true,
                    tension: 0.38,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                    },
                },
            },
        });
    }
}

