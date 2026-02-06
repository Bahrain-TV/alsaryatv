<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">توزيع الحالة</h3>
    
    <!-- Chart container -->
    <div class="h-80 flex items-center justify-center">
        <canvas id="statusDistributionChart" aria-label="رسم بياني لتوزيع الحالة" role="img"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statusDistributionChart').getContext('2d');
        
        // Sample data - in a real implementation, this would come from the backend
        const data = {
            labels: ['مقبول', 'معلق', 'مرفوض', 'فائز'],
            datasets: [{
                label: 'عدد المتصلين',
                data: [65, 15, 10, 10],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)', // Emerald 500
                    'rgba(245, 158, 11, 0.7)',  // Amber 500
                    'rgba(239, 68, 68, 0.7)',   // Red 500
                    'rgba(59, 130, 246, 0.7)'   // Blue 500
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)', // Emerald 500
                    'rgba(245, 158, 11, 1)',  // Amber 500
                    'rgba(239, 68, 68, 1)',   // Red 500
                    'rgba(59, 130, 246, 1)'   // Blue 500
                ],
                borderWidth: 1
            }]
        };
        
        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(156, 163, 175, 1)',
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: '#f3f4f6',
                        bodyColor: '#f3f4f6',
                        borderColor: '#4b5563',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        };
        
        const chart = new Chart(ctx, config);
    });
</script>