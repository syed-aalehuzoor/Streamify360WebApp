<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Video Audiance Insights</h1>
    <div class="bg-white p-6 shadow-lg rounded-lg flex flex-wrap gap-2 justify-center">
        <div id="countriesBarChart" class="flex min-w-[45%] flex-col items-start">
            <h1 class="text-lg font-semibold text-secondary">Top Countries</h1>
            <div class="w-52 self-center">
                <canvas id="chart-0"></canvas>
            </div>
        </div>           
        <div id="citiesBarChart" class="flex min-w-[45%] flex-col items-start">
            <h1 class="text-lg font-semibold text-secondary">Top Countries</h1>
            <div class="w-52 self-center">
                <canvas id="chart-1"></canvas>
            </div>
        </div>
        
        <div id="regionsBarChart" class="flex min-w-[45%] flex-col items-start">
            <h1 class="text-lg font-semibold text-secondary">Top Countries</h1>
            <div class="w-52 self-center">
                <canvas id="chart-2"></canvas>
            </div>
        </div>
        
        <div id="devicesBarChart" class="flex min-w-[45%] flex-col items-start">
            <h1 class="text-lg font-semibold text-secondary">Top Countries</h1>
            <div class="w-52 self-center">
                <canvas id="chart-3"></canvas>
            </div>
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
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [
                {
                    label: 'Views',
                    data: data,
                    backgroundColor : backgroundColors,
                    borderColor: backgroundColors,
                    borderWidth: 1
                }
                ]
            },
            });
        });

    </script>
</x-app-layout>
