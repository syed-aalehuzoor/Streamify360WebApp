<x-admin-panel>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Add New Servers</h1>
            <form action="{{ route('admin-store-server') }}" method="POST" class="bg-white shadow-xl rounded-lg p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="col-span-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    
                    <!-- IP Address -->
                    <div class="col-span-1">
                        <label for="ip" class="block text-sm font-medium text-gray-700">IP Address:</label>
                        <input type="text" id="ip" name="ip" value="{{ old('ip') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('ip')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- SSH Port -->
                    <div class="col-span-1">
                        <label for="ssh_port" class="block text-sm font-medium text-gray-700">SSH Port:</label>
                        <input type="number" id="ssh_port" name="ssh_port" value="22" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('ssh_port')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Username -->
                    <div class="col-span-1">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                        <input type="text" id="username" name="username" value="root" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('username')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Type -->
                    <div class="col-span-1">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type:</label>
                        <select id="type" name="type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="encoder" {{ old('type') == 'encoder' ? 'selected' : '' }}>Encoder</option>
                            <option value="storage" {{ old('type') == 'storage' ? 'selected' : '' }}>Storage</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Domain for Storage -->
                    <div id="domain-container" class="col-span-1 hidden">
                        <label for="domain" class="block text-sm font-medium text-gray-700">Domain:</label>
                        <input type="text" id="domain" name="domain" value="{{ old('domain') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('domain')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Limit and Encoder Type for Encoder -->
                    <div id="limit-container" class="col-span-2 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="limit" class="block text-sm font-medium text-gray-700">Limit:</label>
                                <input type="number" id="limit" name="limit" value="10" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('limit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="encoder_type" class="block text-sm font-medium text-gray-700">Encoder Type:</label>
                                <select id="encoder_type" name="encoder_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white">
                                    <option value="cpu" {{ old('encoder_type') == 'cpu' ? 'selected' : '' }}>CPU</option>
                                    <option value="gpu" {{ old('encoder_type') == 'gpu' ? 'selected' : '' }}>GPU</option>
                                </select>
                                @error('encoder_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Public or Dedicated Toggle for Encoder -->
                    <div id="dedicated-toggle-container" class="col-span-1 hidden">
                        <label for="dedicated" class="block text-sm font-medium text-gray-700">Dedicated Server:</label>
                        <div class="flex items-center mt-1">
                            <input type="hidden" name="dedicated" value="0">
                            <input type="checkbox" id="dedicated" name="dedicated" value="1" class="w-6 h-6 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Dedicated</span>
                            @error('dedicated')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    
                    <!-- User ID for Dedicated Servers -->
                    <div id="userid-container" class="col-span-1 hidden">
                        <label for="userid" class="block text-sm font-medium text-gray-700">User ID:</label>
                        <input type="text" id="userid" name="userid" value="{{ old('userid') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('userid')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Authorization Key Section -->
                <div class="relative rounded-lg">
                    <div class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-gray-200">
                        <label for="serverconfig" class="block text-sm font-medium text-gray-700">Authorization Key:</label>
                        <textarea name="serverconfig" id="serverconfig" class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-gray-100">sudo echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCu9F1Su+5hSVyuZYA9xnSiIDCSMhixl4/wkZEk5QghzI0Z2TyM6P4De6qmBGnqOqvVLyQ1VAxUXer175E/wvc+KpV4DFqdp5YtEucTHLXcxq3c7X39TEEzdzQ8EQff5KpanSGvbfI08mxOPpJ5zkyfbE4e/drhXlk0/2j2vYKe6auOr2vE1gpLQhmuHtshRka+RQvxTzxh+0FZnB7VxEw9p0Fsh6z4kLUhLEvVKHv0V32B4FcDKFZ8hThBeyRqCq28VhAg0z95l1E7Rp9V14z1qmyO2hbJlm7wA79bklYvM0J3pYMgNEzJBBzK8F1tzXElFmoXybn1OcCz7xeEBfFGJ7MblP8m7EVuBszrRgnUIdHaG5l/8sbA8HoHxAFNsfhu9JqPMBPDeLDA== rsa-key-20230904"</textarea>
                        <h6 class="text-sm mt-1">
                            Run this command on the server that you're adding as root user.
                        </h6>
                        <button class="absolute top-0 right-0 mt-2 mr-2 bg-secondary text-white px-3 py-1 text-xs rounded" id="copy-button">Copy</button>
                    </div>
                </div>
                
                <!-- Form Submission -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">Save Server</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const domainContainer = document.getElementById('domain-container');
            const limitContainer = document.getElementById('limit-container');
            const dedicatedToggleContainer = document.getElementById('dedicated-toggle-container');
            const useridContainer = document.getElementById('userid-container');
            const dedicatedCheckbox = document.getElementById('dedicated');

            // Function to toggle visibility based on type
            function toggleFields() {
                const type = typeSelect.value;
                if (type === 'storage') {
                    domainContainer.classList.remove('hidden');
                    limitContainer.classList.add('hidden');
                    dedicatedToggleContainer.classList.add('hidden');
                    useridContainer.classList.add('hidden');
                } else if (type === 'encoder') {
                    domainContainer.classList.add('hidden');
                    limitContainer.classList.remove('hidden');
                    dedicatedToggleContainer.classList.remove('hidden');
                }
            }

            // Toggle userID based on dedicated server checkbox
            function toggleUserId() {
                if (dedicatedCheckbox.checked) {
                    useridContainer.classList.remove('hidden');
                } else {
                    useridContainer.classList.add('hidden');
                }
            }

            // Event Listeners
            typeSelect.addEventListener('change', toggleFields);
            dedicatedCheckbox.addEventListener('change', toggleUserId);

            // Initial toggle on page load
            toggleFields();
            toggleUserId();
            
            // Clipboard Copy Functionality
            document.getElementById('copy-button').addEventListener('click', function(event) {
                event.preventDefault();
                const config = document.getElementById('serverconfig').textContent;
                navigator.clipboard.writeText(config).then(function() {
                    alert('Authorization key copied to clipboard');
                }).catch(function(error) {
                    console.error('Failed to copy text: ', error);
                });
            });
        });
    </script>
</x-admin-panel>
