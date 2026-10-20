/**
 * Royal Stay - Luxury Hotel Booking Management System
 * Admin Dashboard JavaScript & Chart.js Integration
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('adminSidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        });
    }

    // 2. Client-side Table Search / Filter
    const searchInputs = document.querySelectorAll('.data-table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', () => {
            const query = input.value.toLowerCase();
            const targetTableId = input.getAttribute('data-target');
            const table = document.getElementById(targetTableId);
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            }
        });
    });

    // 3. Confirm Delete / Status Change Prompts
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // 4. Modal Dialog Handler
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    };
});

/**
 * Initialize Dashboard Charts using Chart.js
 *
 * @param {Array} monthlyLabels
 * @param {Array} monthlyBookings
 * @param {Array} monthlyRevenue
 * @param {Object} statusData
 */
function initDashboardCharts(monthlyLabels, monthlyBookings, monthlyRevenue, statusData) {
    if (typeof Chart === 'undefined') return;

    // Monthly Performance Chart
    const revenueCtx = document.getElementById('monthlyPerformanceChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        label: 'Revenue ($)',
                        data: monthlyRevenue,
                        backgroundColor: 'rgba(217, 164, 65, 0.85)',
                        borderColor: '#D9A441',
                        borderRadius: 6,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Bookings Count',
                        data: monthlyBookings,
                        type: 'line',
                        borderColor: '#063B3E',
                        backgroundColor: 'rgba(6, 59, 62, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#063B3E',
                        pointRadius: 4,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(val) { return '$' + val; }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Status Distribution Doughnut Chart
    const statusCtx = document.getElementById('statusDistributionChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        statusData.confirmed || 0,
                        statusData.pending || 0,
                        statusData.completed || 0,
                        statusData.cancelled || 0
                    ],
                    backgroundColor: [
                        '#10B981',
                        '#F59E0B',
                        '#3B82F6',
                        '#EF4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { family: "'Poppins', sans-serif" }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
}
