<x-app-layout>
    <h1 class="font-semibold text-lg text-gray-800 mb-6">Ads Settings</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 border border-green-300 rounded-lg p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-lg p-8 space-y-6">
        <form action="{{ route('ad-settings.update') }}" class="flex flex-col gap-6" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Allowed Domains Section -->
            <div class="form-group">
                <label for="pop_ads_code" class="font-medium text-gray-700">Pop Ads:</label>
                <textarea cols="30" rows="3" name="pop_ads_code" placeholder="Paste Pop Ads code here..." class="form-control border rounded-md mt-1 p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">{{ is_array(json_decode($settings->pop_ads_code, true)) ? implode(', ', json_decode($settings->pop_ads_code, true)) : $settings->pop_ads_code }}</textarea>
                @error('pop_ads_code')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="vast_link" class="font-medium text-gray-700">Vast Link:</label>
                <x-input type="number" name="vast_link" value="{{ old('vast_link', $settings->vast_link) }}" placeholder="Paste your vast link here..."/>
                @error('vast_link')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end">
                <x-button type="submit" class="w-fit">Save Settings</x-button>
            </div>
        </form>
    </div>
</x-app-layout>