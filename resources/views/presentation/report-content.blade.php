@extends('layouts.guest')

@section('content')
<div class="mx-auto w-96 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Report Abuse</h2>
    
    @if (session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('abuse-reports.store') }}" method="POST">
        @csrf

        <input type="hidden" name="video_id" value="{{ $id }}">

        <div class="mb-4">
            <label class="block font-semibold" for="reason">Reason</label>
            <select name="reason" id="reason" class="w-full border-gray-300 rounded-md shadow-sm">
                <option value="spam">Spam</option>
                <option value="violence">Violence</option>
                <option value="nudity">Nudity</option>
                <option value="copyright">Copyright Violation</option>
                <option value="other">Other</option>
            </select>
            @error('reason')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block font-semibold" for="details">Details</label>
            <textarea name="details" id="details" rows="4" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
            @error('details')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md">Submit Report</button>
    </form>
</div>
@endsection
