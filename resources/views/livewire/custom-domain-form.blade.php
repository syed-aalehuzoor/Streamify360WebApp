<div class="flex flex-col justify-between gap-6 p-6">
    <div class="flex-col">
        <label for="domain" class="text-sm font-medium text-gray-600">Domain:</label>
        <div class="flex flex-col gap-4">
            @error('domain')
                <span class="text-sm px-2 text-red-500">{{ $message }}</span>
            @enderror
            
            @if($verified)
                <div class="text-sm px-2 text-green-500 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Verified.
                </div>
            @endif
            
            <input name="domain" type="text" 
                wire:model="domain" 
                class="px-3 py-2 flex-grow border border-gray-300 rounded-xl focus:outline-none focus:ring focus:ring-indigo-200">
        </div>
    </div>

    @if(!$verified)
        <div wire:poll.60s="checkDomainVerification" class="bg-gray-100 border border-gray-300 p-4 rounded-lg">
            <div class="text-sm text-gray-700 mb-3 flex items-center gap-2">
                <span>Your domain needs verification. This may take a few minutes. Please double-check your DNS settings.</span>
            </div>
            <div class="text-sm text-gray-600">
                <p class="font-semibold">DNS Records:</p>
                <p>dns1.abc.cloudflare.com</p>
                <p>dns2.abc.cloudflare.com</p>
            </div>
        </div>
    @endif
    
    <div class="w-full flex justify-end">
        <x-button wire:click="saveDomain" wire:loading.attr="disabled" class="w-32 relative">
            <span wire:loading.remove>Save</span>
            <span wire:loading>
                <svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
        </x-button>
    </div>
</div>