document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data exists
    if (typeof window.chartData === 'undefined') {
        return;
    }

    // Stock Keeper Charts
    const stockKeeperStockStatusCtx = document.getElementById('stockKeeperStockStatusChart');
    if (stockKeeperStockStatusCtx) {
        new Chart(stockKeeperStockStatusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [
                        window.chartData.stock_status.in_stock,
                        window.chartData.stock_status.low_stock,
                        window.chartData.stock_status.out_of_stock
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const stockKeeperMonthlyTrendsCtx = document.getElementById('stockKeeperMonthlyTrendsChart');
    if (stockKeeperMonthlyTrendsCtx) {
        new Chart(stockKeeperMonthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: window.chartData.monthly_trends.map(item => item.month),
                datasets: [{
                    label: 'Requests',
                    data: window.chartData.monthly_trends.map(item => item.requests),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Approver Charts
    const approverPriorityCtx = document.getElementById('approverPriorityChart');
    if (approverPriorityCtx) {
        new Chart(approverPriorityCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Urgent', 'High', 'Medium', 'Low'],
                datasets: [{
                    data: [
                        window.chartData.urgent_requests.urgent,
                        window.chartData.urgent_requests.high,
                        window.chartData.urgent_requests.medium,
                        window.chartData.urgent_requests.low
                    ],
                    backgroundColor: [
                        '#dc3545',
                        '#fd7e14',
                        '#ffc107',
                        '#6c757d'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const approverMonthlyTrendsCtx = document.getElementById('approverMonthlyTrendsChart');
    if (approverMonthlyTrendsCtx) {
        new Chart(approverMonthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: window.chartData.monthly_trends.map(item => item.month),
                datasets: [{
                    label: 'Requests',
                    data: window.chartData.monthly_trends.map(item => item.requests),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
