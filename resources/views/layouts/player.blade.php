<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/player.js'])
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

        <!-- Styles -->
        @livewireStyles
        @stack('scripts')    
    </head>
    
    <body class="overflow-hidden">  
        @yield('content')
        @livewireScripts
    </body>
</html>
