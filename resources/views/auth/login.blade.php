@extends('layouts.guest')

@section('content')
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-success">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex flex-col items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
                <x-button class="flex justify-center mt-1 w-full p-2 rounded-md">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
        <div class="mt-2">
            <a href="{{ route('auth.google') }}">
                <x-button class="flex gap-2 justify-center w-full p-2 rounded-md">
                    <i class="fa-brands fa-google"></i>
                    <span>Login with Google</span>
                </x-button>
            </a>
        </div>
        <div class="inline-flex items-center justify-center w-full py-2">
            <hr class="w-64 h-px my-8">
            <span class="absolute px-3 font-medium -translate-x-1/2 bg-primary">or</span>
        </div>
        <a href="{{ route('register') }}">
            <x-button class="flex justify-center mt-1 w-full p-2 rounded-md">
                Register
            </x-button>
        </a>
    </x-authentication-card>
@endsection