<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Chunked File Input Test</h1>

    <div class="bg-white shadow rounded-lg">
        @livewire('chunked-file-input', ['filename' => 'example', 'acceptedTypes' => '.mp4'])        
    </div>
</x-app-layout>
