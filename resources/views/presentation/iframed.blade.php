@extends('layouts.player')

@push('meta')
<title>{{ $name }} - {{ $websiteName }}</title>
<link rel="icon" type="image/png" href="{{ $logo }}">
@endpush

@section('content')
    <iframe src="{{$src}}" frameborder="0" class="h-screen w-full" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>
@endsection