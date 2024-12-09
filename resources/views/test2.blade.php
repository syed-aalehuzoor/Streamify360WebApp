<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Chunked File Input Test</h1>

    <div class="bg-white shadow rounded-lg">
        @livewire('chunked-file-input', ['filename' => 'example', 'acceptedTypes' => '.mp4'])        
    </div>


        <h1>Laravel Test API</h1>
    <button id="testApiButton">Test API</button>
    <p id="apiResponse"></p>

    <script>
        document.getElementById('testApiButton').addEventListener('click', function () {
            // Make a GET request to the test API route

            fetch('/api/test-api', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update the DOM with the API response
                document.getElementById('apiResponse').textContent = JSON.stringify(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('apiResponse').textContent = 'An error occurred.';
            });
        });
    </script>

</x-app-layout>
