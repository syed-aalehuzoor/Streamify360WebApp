<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Video Performance Analytics</h1>
    <div class="bg-white p-6 shadow-lg rounded-lg">

        <h2 class="text-center text-xl font-bold">Total Views: {{ $total_views }}</h2>            

        <h3 class="text-lg font-semibold text-secondary mt-4">Views Over Time</h3>

        <form method="GET" action="{{ route('video-performance-trend', ['id' => request('id')]) }}" id="dateRangeForm" class="mt-4">
            <div class="flex items-center space-x-4">
                <input type="text" id="date_range" name="date_range" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Last 28 days" value="{{ request('date_range') }}">
            </div>
        </form>
        <div id="viewsTrendChart" class="mt-4">
            <canvas id="viewsTrend"></canvas>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dateRangeInput = document.getElementById('date_range');
                const form = document.getElementById('dateRangeForm');
                const first_date = @json($first_date);
                const maxDate = new Date();
                const minDate = new Date(first_date);
                flatpickr(dateRangeInput, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    minDate: minDate,
                    maxDate: maxDate,
                    onClose: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                            form.submit();
                        }
                    }
                });
            });
        </script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const viewsTrend = @json($views_trend);

        const labels = viewsTrend.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        const data = viewsTrend.map(item => item.views);
    
        const ctx = document.getElementById('viewsTrend').getContext('2d');
        const chartOptions = { responsive: true, scales: { y: { beginAtZero: true } } };

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Views',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }
                ]
            },
            options: chartOptions
        });
    </script>
</x-app-layout>
