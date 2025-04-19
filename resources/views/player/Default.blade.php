@extends('layouts.player')

@push('meta')
<title>{{ $name }} - {{ $websiteName }}</title>
<link rel="icon" type="image/png" href="{{ $logo }}">
@endpush

@section('content')
<div id="playerWrapper" class="relative w-full" style="background-color:{{ $playerBackground }};color:{{$controlButtons}};">
    <video id="videoPlayer" class="w-full h-full"@if($loop) loop @endif></video>
    <div id="adContainer" class="w-full hidden h-full">
        <video id="advertisment" class="w-full h-full"></video>
        <div class="absolute text-2xl bottom-2 right-2 bg-black text-white px-6 py-2 rounded">
            Ad ends in: <span id="timer">0</span>s
        </div>
    </div>
    <div id="videoThumbnailOverlay" class="absolute text-7xl inset-0 flex items-center justify-center" style="background-color:{{ $thumbnailBackground }};opacity:{{ $poster ? '1' : '0.6' }};">
        @if($poster)
            <img src="{{ $poster }}" class="w-full absolute">
        @endif
        <div id="loader" role="status">
            <svg aria-hidden="true" class="w-12 h-12 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
        </div>
        <div id="initPlay" class="z-50 hidden bg-black/80 cursor-pointer h-full w-full relative">
            <i class="fa-solid fa-play p-3 opacity-100 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></i>
        </div>        
    </div>
    @if($controls)
        <div id="videoControlsOverlay" class="absolute inset-0 hidden flex-col bg-transparent">
            <div id="videoOverlay" class="flex-grow flex items-center text-8xl justify-center bg-transparent"></div>

            <div id="seekBarBackground" class="cursor-pointer pt-5">
                <div class="relative h-1 w-full" style="background-color:{{ $seekBarBackground }};">
                    <div id="seekBarLoadedProgress" class="absolute top-0 left-0 h-full" style="background-color:{{ $seekBarLoadedProgress }};"></div>
                    <div id="seekBarCurrentProgress" class="absolute top-0 left-0 h-full" style="background-color:{{ $seekBarCurrentProgress }};"></div>
                </div>
            </div>

            <div id="bottomControlBar" class="bg-transparent flex items-center justify-between text-2xl">
                <div id="leftButtons" class="flex">
                    <i id='bottomLeftPlayPause' class="fa-solid fa-play p-3"></i>
                    <div class="items-center hidden sm:flex group">
                        <i id="volumeButton" class="fa-solid fa-volume-low p-3"></i>
                        <div id="volumeSeek" class="h-1 w-16 child hidden group-hover:block" style="background-color:{{ $volumeSeekBackground }};">
                            <div id="currentVolume" class="w-1/2 h-1 relative" style="background-color:{{ $volumeSeek }};">
                                <div class="absolute h-3 w-3 rounded-full right-0 top-1/2 -translate-y-1/2 translate-x-1/2"  style="background-color:{{ $volumeSeek }};"></div>
                            </div>
                        </div>
                    </div>
                    <span id="timeStamp" class="text-base p-3">00:00 / 00:00</span>
                </div>
                <div id="rightButtons" class="flex relative text-2xl">
                    <div id="menu" class="h-60 w-60 p-2 hidden absolute right-2 bottom-12 rounded-md" style="background-color:{{ $menuBackground }};color:{{$menuText}};" x-data="{ activeTab: 1 }">
                        <div id="menuButtons" class="text-md flex" style="color:{{ $controlButtons }};">
                            <button class="fa-solid fa-sliders p-2" @click="activeTab = 1"></button>
                            @if($showPlaybackSpeed)
                                <button class="fa-regular fa-circle-play p-2" @click="activeTab = 2"></button>
                            @endif
                            </div>
                        <hr>
                        <div id="menuMain" class="text-base">
                            <div id="qualities" class="flex flex-col" x-show="activeTab === 1">
                                <h1>Quality:</h1>
                            </div>
                            @if($showPlaybackSpeed)
                                <div id="speeds" class="flex flex-col"  x-show="activeTab === 2">
                                    <h1>Playback Speed:</h1>
                                </div>
                            @endif
                        </div>
                    </div>
                    <a class="p-3" title="{{ $websiteURL }}" href="{{ $websiteURL }}" target="_blank">
                        <img src="{{ $logo }}" alt="{{ $websiteURL }}" class="h-[1em]">
                    </a>
                    <i id="pictureInPicture" class="fa-solid fa-window-restore p-3" title="Picture-In-Picture Mode"></i>
                    <i id="settingsButton" class="fa-solid fa-gear p-3" title="Settings"></i>
                    <i id="fullscreenButton" class="fa-solid fa-expand p-3" title="Fullscreen"></i>
                </div>
                
            </div>
        </div>
    @endif
</div>

<div id="contextMenu" class="absolute hidden py-2 min-w-48 rounded-sm" style="background-color:{{ $playerBackground }};color:{{$controlButtons}};">
    <div class="px-4 py-1">
        <a title="{{ env('APP_NAME') }}" href="{{ env('APP_URL') }}" target="_blank">
            {{ env('APP_NAME') }}
        </a>
    </div>
    <div class="px-4 py-1">
        <a href="{{ route('abuse.report', ['id' => $id]) }}">Report Abuse</a>
    </div>
</div>
@push('scripts')
@if ( $popAdsCode )
{!! $popAdsCode !!}
@endif
<script>
    
    let playerState = {
        srcURL: @json($src),
        vastURL: @json($vastlink),
        playing: false,
        duration: 0,
        buffered: 0,
        currentTime: 0,
        muted: @json($muted),
        volume: @json($volume),
        fullscreen: false,
        pictureInPicture: false,
        currentResolution: '360p',
        availableResolutions: [],
        currentPlaybackSpeed: @json($playbackSpeed),
        playbackSpeeds: @json($customPlaybackSpeeds),
        started: false,
        mouseMoveTimeout: null,
        throttleTimeout: false,
        menuActive: @json($menuActive),
        menuActiveText: @json($menuActiveText),
        menuBackground: @json($menuBackground),
        menuText: @json($menuText)
    };
</script>
@endpush
@endsection