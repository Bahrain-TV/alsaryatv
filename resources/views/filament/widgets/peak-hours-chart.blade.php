<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">أوقات الذروة</h3>
    
    <!-- Chart container -->
    <div class="h-80">
        <canvas id="peakHoursChart" aria-label="رسم بياني لأوقات الذروة" role="img"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('peakHoursChart').getContext('2d');
        
        // Sample data - in a real implementation, this would come from the backend
        const data = {
            labels: ['12ص', '1ص', '2ص', '3ص', '4ص', '5ص', '6ص', '7ص', '8ص', '9ص', '10ص', '11ص', '12م', '1م', '2م', '3م', '4م', '5م', '6م', '7م', '8م', '9م', '10م', '11م'],
            datasets: [{
                label: 'عدد المتصلين',
                data: [5, 4, 6, 8, 12, 15, 18, 22, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 70, 60, 50, 40],
                backgroundColor: [
                    'rgba(245, 158, 11, 0.7)', // Amber 500
                    'rgba(245, 158, 11, 0.6)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(245, 158, 11, 0.3)',
                    'rgba(245, 158, 11, 0.3)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(245, 158, 11, 0.6)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(245, 158, 11, 0.9)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 0.9)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(245, 158, 11, 0.6)',
                    'rgba(245, 158, 11, 0.5)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(245, 158, 11, 0.3)',
                    'rgba(245, 158, 11, 0.3)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(245, 158, 11, 0.5)'
                ],
                borderColor: [
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: '#f3f4f6',
                        bodyColor: '#f3f4f6',
                        borderColor: '#4b5563',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `عدد المتصلين: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(156, 163, 175, 0.8)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(156, 163, 175, 0.8)',
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        };
        
        const chart = new Chart(ctx, config);
    });
</script>