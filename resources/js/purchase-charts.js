document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data exists
    if (typeof window.purchaseChartData === 'undefined') {
        return;
    }

    // Monthly Stock Trends Chart (Line)
    const monthlyTrendsCtx = document.getElementById('monthlyStockTrendsChart');
    if (monthlyTrendsCtx) {
        new Chart(monthlyTrendsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: window.purchaseChartData.monthly_stock_trends.map(item => item.month),
                datasets: [{
                    label: 'Stock Added',
                    data: window.purchaseChartData.monthly_stock_trends.map(item => item.stock_added),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
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
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity Added'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }

    // Categories by Value Chart (Horizontal Bar)
    const categoriesValueCtx = document.getElementById('categoriesValueChart');
    if (categoriesValueCtx) {
        const categoryLabels = window.purchaseChartData.categories.map(item => item.category);
        const categoryValues = window.purchaseChartData.categories.map(item => item.value);
        
        new Chart(categoriesValueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Stock Value ($)',
                    data: categoryValues,
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
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Stock Value ($)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Category'
                        }
                    }
                }
            }
        });
    }

    // Stock Status Distribution Chart (Doughnut)
    const stockStatusCtx = document.getElementById('stockStatusChart');
    if (stockStatusCtx) {
        new Chart(stockStatusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [
                        window.purchaseChartData.stock_status.in_stock,
                        window.purchaseChartData.stock_status.low_stock,
                        window.purchaseChartData.stock_status.out_of_stock
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
});
