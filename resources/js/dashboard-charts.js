document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data exists
    if (typeof window.chartData === 'undefined') {
        return;
    }

    // Priority Distribution Chart (Doughnut)
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx) {
        new Chart(priorityCtx.getContext('2d'), {
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

    // Stock Status Chart (Bar)
    const stockStatusCtx = document.getElementById('stockStatusChart');
    if (stockStatusCtx) {
        new Chart(stockStatusCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    label: 'Items',
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
                    borderWidth: 1,
                    borderColor: '#fff'
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

    // Monthly Trends Chart (Line)
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        new Chart(monthlyTrendsCtx.getContext('2d'), {
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

    // Categories Chart (Horizontal Bar)
    const categoriesCtx = document.getElementById('categoriesChart');
    if (categoriesCtx) {
        const categoryLabels = Object.keys(window.chartData.categories);
        const categoryData = Object.values(window.chartData.categories);
        
        new Chart(categoriesCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Items',
                    data: categoryData,
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1'
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
