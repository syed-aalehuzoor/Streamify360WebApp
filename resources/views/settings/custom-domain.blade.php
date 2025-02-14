@extends('layouts.app')

@section('pageHeading')
    Setup Custom Domain
@endsection

@section('content')

    <form action="{{ route('settings.update', 'custom-domain') }}" method="POST" class="p-6 flex flex-col gap-4">
        @csrf
        @method('PUT')
        <div class="flex flex-col">
            <label for="player_domain">Domain:</label>
            <x-input 
                name="player_domain" 
                id="player_domain" 
                type="text" 
                value="{{ old('player_domain', $settings->player_domain) }}">
            </x-input>
        </div>
        <div class="flex justify-end">
            <x-button>Save</x-button>
        </div>
    </form>

    @if (!$settings->player_domain_varified)
        @push('notifications')
            <div class="p-4 mb-4 bg-blue-50 border-l-4 border-blue-400 rounded-lg text-blue-900 space-y-2">
                <div class="font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    Domain Verification in Progress
                </div>
                <p class="text-sm">
                    We're verifying <span class="font-medium">{{ $settings->player_domain }}</span>. This process may take up to 48 hours.
                </p>
                <div class="text-sm bg-blue-100 p-3 rounded-md">
                    <p class="font-medium mb-1">To complete setup:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        Update your DNS settings with:
                        @foreach (config('system.customDomainDNS') as $dns)
                            <li class="font-mono text-blue-700 ml-4">{{ $dns }}</li>                            
                        @endforeach
                    </ol>
                </div>
            </div>
        @endpush
    @endif

@endsection
