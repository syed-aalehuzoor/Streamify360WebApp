<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 mb-4">Video Audience Insights</h1>
    <div class="shadow-lg flex flex-wrap gap-4 justify-center">
        <!-- Top Countries -->
        <div id="countriesBarChart" class="bg-white rounded-lg p-4 flex flex-col items-start w-full sm:w-[calc(50%-1rem)]">
            <h1 class="text-lg font-semibold text-secondary mb-2">Top Countries</h1>
            <canvas id="chart-0"></canvas>
        </div>
    
        <!-- Top Devices -->
        <div id="devicesBarChart" class="bg-white rounded-lg p-4 flex flex-col items-start w-full sm:w-[calc(50%-1rem)]">
            <h1 class="text-lg font-semibold text-secondary mb-2">Top Devices</h1>
            <canvas id="chart-3"></canvas>
        </div>
    
        <!-- Top Cities -->
        <div id="citiesBarChart" class="bg-white rounded-lg p-4 flex flex-col items-start w-full">
            <h1 class="text-lg font-semibold text-secondary mb-2">Top Cities</h1>
            <canvas id="chart-1"></canvas>
        </div>
    
        <!-- Top Regions -->
        <div id="regionsBarChart" class="bg-white rounded-lg p-4 flex flex-col items-start w-full">
            <h1 class="text-lg font-semibold text-secondary mb-2">Top Regions</h1>
            <canvas id="chart-2"></canvas>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const countries = @json($countries);
        const cities = @json($cities);
        const regions = @json($regions);
        const devices = @json($devices);
        const audiance = [countries, cities, regions, devices];

        audiance.forEach((dataSet, index) => {
            const labels = dataSet.map(item => item.name);
            const data = dataSet.map(item => item.percentage);

            const tempElement = document.createElement('div');
            tempElement.className = 'bg-secondary';
            document.body.appendChild(tempElement);
            const [r, g, b] = getComputedStyle(tempElement).backgroundColor
                .match(/\d+/g)
                .map(Number);
            document.body.removeChild(tempElement);

            const backgroundColors = Array.from({ length: 10 }, (_, i) =>
                `rgb(${[r, g, b].map(c => Math.min(255, c * (1 + i * 0.2))).join(", ")})`
            );

            const ctx = document.getElementById(`chart-${index}`).getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                    {
                        label: 'Views',
                        data: data,
                        backgroundColor : backgroundColors,
                        borderColor: backgroundColors,
                        borderWidth: 1,
                        barThickness: 12,
                        maxBarThickness: 15,

                    }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            },
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                }
            });
        });

    </script>
</x-app-layout>
