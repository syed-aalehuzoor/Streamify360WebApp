@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-secondary w-full mt-1 focus:border-secondary focus:ring-secondary rounded-md shadow-sm']) !!}>
