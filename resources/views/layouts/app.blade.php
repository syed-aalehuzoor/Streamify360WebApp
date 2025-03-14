<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    
    <body class="bg-accent flex flex-col items-center h-full" x-data="{openSidebar: false}">  
        @livewire('header')
        
        <div class="flex justify-center gap-12 min-h-full w-full sm:w-3/4 sm:m-10 flex-row">
    
                <aside class="bg-primary w-96 z-20 hidden shadow-xl sm:block min-h-full rounded-lg" id="sidebar-multi-level-sidebar">
                    <x-nav-menu nav="navigation"/>
                </aside>

                <!-- Mobile Navigation -->
                <aside x-show="openSidebar" x-cloak class="bg-primary z-40 left-0 fixed h-full w-60 text-gray-500 sm:hidden">   
                    <x-nav-menu nav="navigation"/>
                </aside>


                <main class="flex flex-col w-full h-full overflow-y-auto">
                    <h1 class="font-semibold text-lg text-gray-800 mb-6">
                        @yield('pageHeading')
                    </h1>
                    @stack('notifications')
                    <x-notification/>
                    <div class="bg-white shadow-xl rounded-lg text-sm overflow-hidden">
                        @yield('content')
                    </div>
                </main>

                @stack('modals')
                @livewireScripts

        </div>
        
    </body>
</html>
