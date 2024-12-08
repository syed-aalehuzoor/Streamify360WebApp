<x-app-layout>        

        <h1 class="font-semibold text-md text-gray-800 m-2">Edit User</h1>

        <!-- Display any success or error messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-lg shadow">
                {{ session('success') }}
            </div>
        @endif
    
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    
        <!-- Edit user Form -->
        <form action="{{ route('users.edit', $user->id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg flex flex-col gap-4 p-4">
            @csrf

            <!-- user Name -->
            <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-gray-700">User Name:</label>
                <input type="text" name="username" id="username" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('username', $user->name) }}">
            </div>
            
            <!-- user Email -->
            <div class="space-y-2">
                <label for="useremail" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="text" name="useremail" id="useremail" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('email', $user->email) }}">
            </div>

            <!-- user Plan -->
            <div class="space-y-2">
                <label for="userplan" class="block text-sm font-medium text-gray-700">User Plan:</label>
                <input type="text" name="userplan" id="userplan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('userplan', $user->userplan) }}">
            </div>
            
            <!-- user Type -->
            <div class="space-y-2">
                <label for="usertype" class="block text-sm font-medium text-gray-700">User Type:</label>
                <input type="text" name="usertype" id="usertype" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('usertype', $user->usertype) }}">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center px-4 py-2 bg-secondary border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>

</x-app-layout>
