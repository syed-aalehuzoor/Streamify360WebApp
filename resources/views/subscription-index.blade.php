<x-app-layout>
    <h1 class="font-semibold text-lg text-gray-800 mb-1">Subscription</h1>
    <div class="bg-gray-50 shadow-md rounded-lg p-8 max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-gray-800 mb-4">Welcome, {{ $user->name }}!</h1>
        <p class="text-lg text-gray-600">Your Subscription Plan:</p>
        <p class="text-4xl font-bold text-blue-500 capitalize mt-2">{{ ucfirst($user->userplan) }}</p>
        
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="text-sm text-gray-500">
                To upgrade, please contact 
                <a href="https://t.me/yahyahanif" class="text-blue-600 hover:text-blue-700 font-medium">
                    @yahyahanif
                </a> 
                on Telegram.
            </p>
        </div>
    </div>
    
</x-app-layout>
