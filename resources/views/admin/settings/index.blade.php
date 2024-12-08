<x-admin-panel>
    <h1 class="font-semibold text-lg text-gray-800 mb-6">Settings</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 border border-green-300 rounded-lg p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-lg p-8 space-y-6">
        <form action="{{ route('config-setting.update') }}" class="flex flex-col gap-6" method="POST">
            @csrf
            @method('POST')

            <!-- Loop through the settings dynamically -->
            @foreach($settings as $setting)
                <div class="form-group">
                    <label for="{{ $setting->key }}" class="font-medium text-gray-700">
                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}:
                    </label>

                    <x-input type="text" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" placeholder="Enter {{ $setting->key }} here..." class="form-control border rounded-md mt-1 p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" />

                    @error($setting->key)
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach

            <div class="flex justify-end">
                <x-button type="submit" class="w-fit">Save Settings</x-button>
            </div>
        </form>
    </div>
</x-admin-panel>
