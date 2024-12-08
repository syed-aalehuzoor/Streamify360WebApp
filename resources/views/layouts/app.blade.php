<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    
    </head>
    
    <body class="bg-accent flex flex-col items-center h-full" x-data="{openSidebar: false}">  
        @livewire('header')
        
        <div class="flex justify-center gap-12 min-h-full w-full sm:w-3/4 sm:m-10 flex-row">
    
                <x-navigation />
            
                <!-- Page Content -->
                <main class="flex flex-col w-full h-full overflow-y-auto">
                    
                    {{ $slot }}    

                </main>

                @stack('modals')
                @livewireScripts

        </div>
        
    </body>
</html>
