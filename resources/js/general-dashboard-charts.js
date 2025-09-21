document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data exists
    if (typeof window.chartData === 'undefined') {
        return;
    }

    // General Stock Status Chart (Bar)
    const generalStockStatusCtx = document.getElementById('generalStockStatusChart');
    if (generalStockStatusCtx) {
        new Chart(generalStockStatusCtx.getContext('2d'), {
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

    // General Categories Chart (Horizontal Bar)
    const generalCategoriesCtx = document.getElementById('generalCategoriesChart');
    if (generalCategoriesCtx) {
        const categoryLabels = Object.keys(window.chartData.categories);
        const categoryData = Object.values(window.chartData.categories);
        
        new Chart(generalCategoriesCtx.getContext('2d'), {
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
