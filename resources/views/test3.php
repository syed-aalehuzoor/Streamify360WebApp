<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HLS Video Player</title>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 p-0 h-screen w-screen overflow-hidden">


    <script>
        // Helper function to create elements
        function createElement(tag, attributes) {
            const element = document.createElement(tag);
            Object.entries(attributes).forEach(([key, value]) => {
                if (key === 'textContent') {
                    element.textContent = value;
                } else {
                    element[key] = value;
                }
            });
            return element;
        }
        // Wait for the DOM to fully load
        const body = document.body;

        const config = {
            videoUrl: "https://streambox.streamify360.net/streams/JufsMzU4yQf/360p.m3u8", // Replace with your HLS video URL
            isMobile: window.innerWidth <= 768 // Example condition for mobile
        };


        // Function to toggle seek bar visibility
        function toggleSeekbar(highlight) {
            const seekDot = document.getElementById('seekDot');
            seekDot.classList.toggle('hidden', !highlight);
            tooltipElement.classList.toggle('hidden', !highlight);
            seekBarContainer.classList.toggle('pt-[10px]', highlight);
            seekBarContainer.classList.toggle('pt-3', !highlight);
        }

        // Function to handle seek bar mouse move
        function handleSeekBarMouseMove(e) {
            const rect = seekBarBackground.getBoundingClientRect();
            const offsetX = e.clientX - rect.left;
            const percentage = offsetX / rect.width;
            const time = percentage * videoElement.duration;
            tooltipElement.style.left = `${e.clientX}px`;
            tooltipElement.style.top = `${rect.top - 25}px`;
            tooltipElement.textContent = formatTime(time);
            tooltipElement.classList.remove('hidden');
        }

        // Function to handle play/pause button click
        function handlePlayPauseButtonClick() {
            videoElement.paused ? videoElement.play() : videoElement.pause();
            playPauseButton.classList.toggle('fa-play');
            playPauseButton.classList.toggle('fa-pause');
        }

        // Function to update seek bar as video plays
        function updateSeekBar() {
            seekBarProgress.style.width = `${(videoElement.currentTime / videoElement.duration) * 100}%`;
            timeDisplayElement.textContent = `${formatTime(videoElement.currentTime)} / ${formatTime(videoElement.duration)}`;
            seekBarProgress.title = formatTime(videoElement.currentTime);
        }

        // Function to handle seek bar click
        function handleSeekBarClick(e) {
            const rect = seekBarBackground.getBoundingClientRect();
            const position = e.clientX - rect.left;
            const percentage = position / seekBarBackground.offsetWidth;
            videoElement.currentTime = percentage * videoElement.duration;
            tooltipElement.textContent = formatTime(videoElement.currentTime);
        }

        // Function to handle fullscreen toggle
        function handleFullscreenToggle() {
            const videoContainer = document.getElementById('videoContainer');
            const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement;

            if (isFullscreen) {    
                document.exitFullscreen?.() || document.webkitExitFullscreen?.() || document.msExitFullscreen?.();
                fullscreenButton.classList.remove('fa-compress');
                fullscreenButton.classList.add('fa-expand');        
            } else {
                videoContainer.requestFullscreen?.() || videoContainer.webkitRequestFullscreen?.() || videoContainer.msRequestFullscreen?.();
                fullscreenButton.classList.remove('fa-expand');
                fullscreenButton.classList.add('fa-compress');
            }
        }

        // Function to handle volume toggle
        function handleVolumeToggle() {
            videoElement.muted = !videoElement.muted;
            volumeToggleIcon.classList.toggle('fa-volume-xmark', videoElement.muted);
            volumeToggleIcon.classList.toggle('fa-volume-high', !videoElement.muted);
        }

        // Function to handle volume change
        function handleVolumeChange() {
            videoElement.volume = volumeBar.value;
        }

        // Function to show volume bar on hover
        function showVolumeBarOnHover() {
            volumeBar.classList.remove('hidden');
        }

        // Function to hide volume bar on mouse out
        function hideVolumeBarOnMouseOut() {
            volumeBar.classList.add('hidden');
        }

        // Function to format time
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        }

        function renderCommonFeatures() {
            // Create and configure the main container div
            const videoContainer = createElement('div', { id: 'videoContainer', className: 'video-container h-full w-full overflow-hidden bg-black relative flex' });
            const videoElement = createElement('video', { id: 'videoElement', preload: 'metadata', className: 'w-full h-full overflow-hidden' });
            const playerOverlay = createElement('div', { id: 'playerOverlay', className: 'absolute text-white top-0 left-0 w-full h-full flex flex-col justify-end overflow-hidden' });
            const seekBarContainer = createElement('div', { id: 'seekBarContainer', className: 'flex h-4 pt-3 items-center cursor-pointer bg-transparent' });
            const tooltipElement = createElement('div', { id: 'tooltipElement', className: 'absolute bg-black text-white text-xs rounded p-1 hidden' });
            const seekBarBackground = createElement('div', { id: 'seekBarBackground', className: 'bg-gray-600 w-full h-full relative flex items-center' });
            const barLoaded = createElement('div', { id: 'barLoaded', className: 'bg-gray-400 absolute w-0 h-full' });
            const seekBarProgress = createElement('div', { id: 'seekBarProgress', className: 'h-full w-0 absolute flex items-center bg-red-600' });
            const seekDot = createElement('div', { id: 'seekDot', className: 'h-3 w-3 hidden bg-red-600 absolute top-1/2 right-0 translate-x-1/2 -translate-y-1/2 transform rounded-md' });
            const controlsContainer = createElement('div', { className: 'flex flex-row justify-between h-9' });
            const leftControls = createElement('div', { className: 'flex flex-row gap-1 h-full items-center' });
            const playPauseButton = createElement('i', { id: 'playPauseButton', className: 'fa-solid fa-play aspect-square flex items-center h-full justify-center' });
            const volumeControlsContainer = createElement('div', { id: 'volumeControlsContainer', className: 'flex items-center h-full gap-2' });
            const volumeToggleIcon = createElement('i', { id: 'volumeToggleIcon', className: 'fa-solid fa-volume-high aspect-square flex items-center h-full justify-center' });
            const volumeBar = createElement('input', { type: 'range', id: 'volumeBar', min: '0', max: '1', step: '0.1', className: 'top-0 left-0 w-20 h-full hidden' });
            const timeDisplayElement = createElement('span', { id: 'timeDisplayElement', textContent: '0:00 / 0:00' });
            const rightControls = createElement('div', { className: 'flex flex-row gap-1 h-full items-center' });
            const fullscreenButton = createElement('i', { id: 'fullscreenButton', className: 'fa-solid fa-expand aspect-square flex items-center h-full justify-center' });

            // Append elements
            body.appendChild(videoContainer);
            videoContainer.append(videoElement, playerOverlay);
            playerOverlay.append(seekBarContainer, controlsContainer);
            seekBarContainer.append(seekBarBackground);
            seekBarBackground.append(barLoaded, seekBarProgress);
            seekBarProgress.append(seekDot);
            controlsContainer.append(leftControls, rightControls);
            leftControls.append(playPauseButton, volumeControlsContainer, timeDisplayElement);
            volumeControlsContainer.append(volumeToggleIcon, volumeBar);
            rightControls.append(fullscreenButton);

            // Initialize HLS player
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(config.videoUrl);
                hls.attachMedia(videoElement);
                hls.on(Hls.Events.MANIFEST_PARSED, () => {});
            } else if (videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                videoElement.src = config.videoUrl;
            } else {
                console.error('HLS not supported in this browser.');
            }

            // Event listeners
            seekBarContainer.addEventListener('mousemove', () => toggleSeekbar(true));
            seekBarContainer.addEventListener('mouseleave', () => toggleSeekbar(false));
            seekBarBackground.addEventListener('mousemove', handleSeekBarMouseMove);
            playPauseButton.addEventListener('click', handlePlayPauseButtonClick);
            videoElement.addEventListener('timeupdate', updateSeekBar);
            seekBarContainer.addEventListener('click', handleSeekBarClick);
            fullscreenButton.addEventListener('click', handleFullscreenToggle);
            volumeToggleIcon.addEventListener('click', handleVolumeToggle);
            volumeBar.addEventListener('input', handleVolumeChange);
            volumeControlsContainer.addEventListener('mouseover', showVolumeBarOnHover);
            volumeControlsContainer.addEventListener('mouseout', hideVolumeBarOnMouseOut);
            
            // Update loaded bar
            videoElement.addEventListener('progress', () => {
                if (videoElement.buffered.length > 0) {
                    const bufferedEnd = videoElement.buffered.end(videoElement.buffered.length - 1);
                    const duration = videoElement.duration;
                    const loadedPercentage = (bufferedEnd / duration) * 100;
                    barLoaded.style.width = `${loadedPercentage}%`;
                }
            });
        }

        function renderMobileFeatures() {
            const videoOverlayControls = createElement('div', { className: 'h-full w-full gap-6 flex items-center justify-center' });
            const seekForwardButton = createElement('i', {className:'fa-solid text-white bg-zinc-800/80 fa-forward h-16 w-16 flex items-center justify-center rounded-full'});
            const overlayPlayPauseButton = createElement('i', {className:'fa-solid text-white bg-zinc-800/80 fa-play h-16 w-16 flex items-center justify-center rounded-full'});
            const seekBackwardButton = createElement('i', {className:'fa-solid text-white bg-zinc-800/80 fa-backward h-16 w-16 flex items-center justify-center rounded-full'});
            videoOverlayControls.append(seekBackwardButton,overlayPlayPauseButton, seekForwardButton);
            playerOverlay.insertBefore(videoOverlayControls, playerOverlay.firstChild);
            overlayPlayPauseButton.addEventListener('click', handlePlayPauseButtonClick)
            videoOverlayControls.addEventListener('touchstart', (event)=>{
                if(event.target === this){
                    videoOverlayControls.classList.toggle('invisible');
                    if(!videoElement.paused)
                        setTimeout(() => {
                            videoOverlayControls.classList.add('invisible');
                        }, 5000);
                }
            });
        }

        function renderDesktopFeatures(clickOnVideoPlayPause = false) {
            if (clickOnVideoPlayPause) {
                playerOverlay.addEventListener('click', (event) => {
                    if (event.target === event.currentTarget) {
                        videoElement.paused ? videoElement.play() : videoElement.pause();
                        playPauseButton.classList.toggle('fa-play');
                        playPauseButton.classList.toggle('fa-pause');
                    }
                });
            }
        }

        // Call the render functions
        renderCommonFeatures();
        if (config.isMobile) {
            renderMobileFeatures();
        } else {
            renderDesktopFeatures(clickOnVideoPlayPause = true);
        }
    </script>
</body>
</html>