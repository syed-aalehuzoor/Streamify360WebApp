<div id="playerWrapper" class="relative w-full" style="background-color:{{ $playerBackground }};color:{{$controlButtons}};">
    <video id="videoPlayer" class="w-full h-full"@if($loop) loop @endif></video>

    <div id="videoThumbnailOverlay" class="absolute text-7xl inset-0 flex items-center justify-center" style="background-color:{{ $thumbnailBackground }};opacity:{{ $poster ? '1' : '0.6' }};">
        @if($poster)
            <img src="{{ $poster }}" class="w-full absolute">
        @endif
        <div id="loader" role="status">
            <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
        </div>
        <i class="fa-solid hidden fa-play p-3 cursor-pointer opacity-100 z-50" id="initPlay"></i>
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
                            <button class="fa-regular fa-circle-play p-2" @click="activeTab = 2"></button>
                        </div>
                        <hr>
                        <div id="menuMain" class="text-base">
                            <div id="qualities" class="flex flex-col" x-show="activeTab === 1">
                                <h1>Quality:</h1>
                            </div>
                            <div id="speeds" class="flex flex-col"  x-show="activeTab === 2">
                                <h1>Playback Speed:</h1>
                            </div>
                        </div>
                    </div>
                    <a class="p-3" title="{{ $website }}" href="{{ $website }}" target="_blank">
                        <img src="{{ $logo }}" alt="{{ $website }}" class="h-[1em]">
                    </a>
                    <i id="pictureInPicture" class="fa-solid fa-window-restore p-3" title="Picture-In-Picture Mode"></i>
                    <i id="settingsButton" class="fa-solid fa-gear p-3" title="Settings"></i>
                    <i id="fullscreenButton" class="fa-solid fa-expand p-3" title="Fullscreen"></i>
                </div>
                
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    let playerState = {
        srcURL: @json($src),
        playing: false,
        duration: 0,
        buffered: 0,
        currentTime: 0,
        muted: false,
        volume: {{ $volume }},
        fullscreen: false,
        pictureInPicture: false,
        currentResolution: '360p',
        availableResolutions: [],
        currentPlaybackSpeed: {{ $playbackSpeed }},
        playbackSpeeds:[0.5, 1, 1.25, 1.5, 2],
        started: false,
        mouseMoveTimeout: null,
        throttleTimeout: false,
    };

    function formatSecondsToTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        const paddedMinutes = String(minutes).padStart(2, '0');
        const paddedSeconds = String(secs).padStart(2, '0');
        
        if (hours > 0) {
            const paddedHours = String(hours).padStart(2, '0');
            return `${paddedHours}:${paddedMinutes}:${paddedSeconds}`;
        } else {
            return `${paddedMinutes}:${paddedSeconds}`;
        }
    }

    document.addEventListener('DOMContentLoaded', ()=>{
        const playerWrapper = document.getElementById('playerWrapper');
        const videoThumbnailOverlay = document.getElementById('videoThumbnailOverlay')
        const loader = document.getElementById('loader');
        const initPlay = document.getElementById('initPlay');

        playerWrapper.style.setProperty('height', `${window.innerHeight}px`);

        const videoElement = document.getElementById('videoPlayer');
        const hls = new Hls();
        hls.attachMedia(videoElement);
        if (Hls.isSupported()){
            hls.loadSource(playerState.srcURL);
        }

        function initiatePlay(){
            videoThumbnailOverlay.classList.replace('flex', 'hidden');
            playerState.playing = true;
            playerState.playing ? videoElement.play() : videoElement.pause();
        }

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            videoElement.addEventListener('loadeddata', () => {
                playerState.duration = videoElement.duration.toFixed(2);
                initPlay.classList.remove('hidden');
                loader.classList.add('hidden');
                videoThumbnailOverlay.addEventListener('click', initiatePlay);
            });
        });

        @if($controls)
            const videoControlsOverlay = document.getElementById('videoControlsOverlay')
            const seekBarCurrent = document.getElementById('seekBarCurrentProgress');
            const fullscreenButton = document.getElementById('fullscreenButton');
            const videoSeek = document.getElementById('seekBarBackground');
            const volumeSeek = document.getElementById("volumeSeek");
            const bottomLeftPlayPause = document.getElementById('bottomLeftPlayPause');
            const volumeButton = document.getElementById('volumeButton');
            const qualitiesDiv = document.getElementById('qualities');
            const bottomControlBar = document.getElementById('bottomControlBar');
            const menu = document.getElementById('menu');
            const currentVolume = document.getElementById("currentVolume");
            const timeStamp = document.getElementById('timeStamp');
            const pictureInPicture = document.getElementById('pictureInPicture');
            const settingsButton = document.getElementById('settingsButton');
            const videoOverlay = document.getElementById('videoOverlay');

            videoThumbnailOverlay.addEventListener('click', () => {
                videoControlsOverlay.classList.replace('hidden', 'flex');
            });

            hls.on(Hls.Events.MANIFEST_PARSED, () => {

                playerState.availableResolutions = hls.levels;
                const createQualityButton = (text) => {
                    const button = document.createElement('button');
                    button.textContent = text;
                    button.className = 'w-full text-left px-2';
                    button.onclick = () => setQuality(text);
                    qualitiesDiv.appendChild(button);
                };
                playerState.availableResolutions.forEach((level, index) => createQualityButton(`${level.height}p`));
                createQualityButton('Auto');
                setQuality(playerState.currentResolution);

            });

            function togglePlay() {
                playerState.playing = !playerState.playing;
                playerState.playing ? videoElement.play() : videoElement.pause();
                bottomLeftPlayPause.classList.replace(
                    playerState.playing ? 'fa-play' : 'fa-pause', 
                    playerState.playing ? 'fa-pause' : 'fa-play'
                );
                
                const showControls = () => {
                    if (!playerState.throttleTimeout) {
                        bottomControlBar.classList.remove('hidden');
                        clearTimeout(playerState.mouseMoveTimeout);
                        playerState.throttleTimeout = true;
                        setTimeout(() => {playerState.throttleTimeout = false;}, 200);
                        playerState.mouseMoveTimeout = setTimeout(() => {bottomControlBar.classList.add('hidden');}, 4000);
                    }
                };

                if (playerState.playing){
                    playerWrapper.addEventListener('mousemove', showControls);
                } else {
                    playerWrapper.removeEventListener('mousemove', showControls);
                    bottomControlBar.classList.remove('hidden');
                }
            }

            function toggleSettings() {
                menu.classList.toggle('hidden');
            }

            async function togglePiP() {
                if (videoElement !== document.pictureInPictureElement) {
                    await videoElement.requestPictureInPicture();
                    playerState.pictureInPicture = true;
                    videoElement.addEventListener('leavepictureinpicture', () => {
                        playerState.pictureInPicture = false;
                    });
                } else {
                    await document.exitPictureInPicture();
                }
            }

            function setQuality(quality){
                const index = playerState.availableResolutions.findIndex(resolution => `${resolution.height}p` === quality);
                playerState.currentResolution = index;

                hls.currentLevel = index;
                const qualityButtons = qualitiesDiv.querySelectorAll('button');
                qualityButtons.forEach((button, buttonIndex) => {
                    if (buttonIndex === index || (index === -1 && button.textContent === 'Auto')) {
                        button.style.backgroundColor = '{{ $menuActive }}';
                        button.style.color = '{{ $menuActiveText }}';
                    } else {
                        button.style.backgroundColor = '{{ $menuBackground }}';
                        button.style.color = '{{ $menuText }}';
                    }
                });
                menu.classList.add('hidden');
            }

            @if($customPlaybackSpeed)
                const speedsDiv = document.getElementById('speeds');
                const setPlaybackSpeed = (speed) => {
                    const speedButtons = speedsDiv.querySelectorAll('button');
                    speedButtons.forEach((button)=>{
                        if(button.textContent === `${speed}x`){
                            button.style.backgroundColor = '{{ $menuActive }}';
                            button.style.color = '{{ $menuActiveText }}';
                        } else{
                            button.style.backgroundColor = '{{ $menuBackground }}';
                            button.style.color = '{{ $menuText }}';
                        }
                    });
                    playerState.currentPlaybackSpeed = speed;
                    videoElement.playbackRate = speed;
                    menu.classList.add('hidden');
                };
                const createSpeedButton = (speed) => {
                        const button = document.createElement('button');
                        button.textContent = `${speed}x`;
                        button.className = 'w-full text-left px-2';
                        button.onclick = () => setPlaybackSpeed(speed);
                        speedsDiv.appendChild(button);
                    };

                playerState.playbackSpeeds.forEach(speed => createSpeedButton(speed));
                setPlaybackSpeed(playerState.currentPlaybackSpeed);
            @endif
            
            function toggleFullScreen() {
                playerState.fullscreen = !playerState.fullscreen;
                const enterFullscreen = () => {
                    if (playerWrapper.requestFullscreen) playerWrapper.requestFullscreen();
                    else if (playerWrapper.mozRequestFullScreen) playerWrapper.mozRequestFullScreen(); // Firefox
                    else if (playerWrapper.webkitRequestFullscreen) playerWrapper.webkitRequestFullscreen(); // Chrome, Safari, Opera
                    else if (playerWrapper.msRequestFullscreen) playerWrapper.msRequestFullscreen(); // IE/Edge
                };

                const exitFullscreen = () => {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.mozCancelFullScreen) document.mozCancelFullScreen(); // Firefox
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen(); // Chrome, Safari, Opera
                    else if (document.msExitFullscreen) document.msExitFullscreen(); // IE/Edge
                };

                if (playerState.fullscreen && document.fullscreenElement !== playerWrapper) {
                    enterFullscreen();
                    fullscreenButton.classList.replace('fa-expand', 'fa-compress');
                } else if (!playerState.fullscreen && document.fullscreenElement === playerWrapper) {
                    exitFullscreen();
                    fullscreenButton.classList.replace('fa-compress', 'fa-expand');
                }
            }

            function toggleMute() {
                playerState.muted = !playerState.muted;
                videoElement.muted = playerState.muted;
                volumeButton.classList.toggle('fa-volume-xmark', playerState.muted);
                volumeButton.classList.toggle('fa-volume-low', !playerState.muted);
            }

            const updateSeek = (clientX, seekElement, callback) => {
                const rect = seekElement.getBoundingClientRect();
                const offsetX = clientX - rect.left;
                const width = rect.width;
                const ratio = Math.min(Math.max(offsetX / width, 0), 1); // Normalize to [0, 1]
                callback(ratio);
            };

            const handleSeek = (startEvent, moveEvent, endEvent, seekElement, updateCallback) => {
                const moveHandler = (e) =>
                    updateSeek(e.clientX || e.touches[0].clientX, seekElement, updateCallback);
                const endHandler = () => {
                    document.removeEventListener(moveEvent, moveHandler);
                    document.removeEventListener(endEvent, endHandler);
                };
                updateSeek(startEvent.clientX || startEvent.touches[0].clientX, seekElement, updateCallback);
                document.addEventListener(moveEvent, moveHandler);
                document.addEventListener(endEvent, endHandler);
            };

            const updateVolume = (ratio) => {
                const volume = ratio;
                playerState.volume = volume;
                currentVolume.style.width = `${Math.round(volume * 100)}%`;
                videoElement.volume = volume;
            };
            updateVolume(playerState.volume);

            const updateVideoTime = (ratio) => {
                const currentTime = ratio * playerState.duration;
                playerState.currentTime = Math.round(currentTime);

                seekBarCurrent.style.width = `${Math.round(ratio * 100)}%`;
                videoElement.currentTime = currentTime;
            };

            volumeSeek.addEventListener("mousedown", (e) =>
                handleSeek(e, "mousemove", "mouseup", volumeSeek, updateVolume)
            );
            volumeSeek.addEventListener("touchstart", (e) =>
                handleSeek(e, "touchmove", "touchend", volumeSeek, updateVolume)
            );

            videoSeek.addEventListener("mousedown", (e) =>
                handleSeek(e, "mousemove", "mouseup", videoSeek, updateVideoTime)
            );
            videoSeek.addEventListener("touchstart", (e) =>
                handleSeek(e, "touchmove", "touchend", videoSeek, updateVideoTime)
            );

            videoElement.addEventListener('timeupdate', ()=>{
                playerState.currentTime = Math.round(videoElement.currentTime);            
                const progressPercent = (playerState.currentTime / playerState.duration) * 100;
                const loadedPercent = (videoElement.buffered.length > 0) 
                    ? (videoElement.buffered.end(0) / playerState.duration) * 100 
                    : 0;            
                seekBarCurrent.style.width = `${progressPercent}%`;
                timeStamp.innerText = `${formatSecondsToTime(playerState.currentTime)} / ${formatSecondsToTime(playerState.duration)}`;
            });

            settingsButton.addEventListener('click', toggleSettings);
            pictureInPicture.addEventListener('click', togglePiP);
            fullscreenButton.addEventListener('click', toggleFullScreen);
            volumeButton.addEventListener('click', toggleMute);
            bottomLeftPlayPause.addEventListener('click', togglePlay);
            videoOverlay.addEventListener('click', togglePlay);
        @endif
    });
</script>
@endpush
