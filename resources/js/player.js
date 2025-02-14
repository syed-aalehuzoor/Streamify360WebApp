import Hls from 'hls.js';

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

class VideoPlayer {
    constructor(playerState) {
        this.playerState = playerState;
        this.initElements();
        this.initPlayer();
    }

    initElements() {
        this.playerWrapper = document.getElementById('playerWrapper');
        this.videoThumbnailOverlay = document.getElementById('videoThumbnailOverlay');
        this.loader = document.getElementById('loader');
        this.initPlay = document.getElementById('initPlay');
        this.videoElement = document.getElementById('videoPlayer');
        this.adVideo = document.getElementById('advertisment');
        this.playerWrapper.style.setProperty('height', `${window.innerHeight}px`);
        if(document.getElementById('videoControlsOverlay')){
            this.videoControlsOverlay = document.getElementById('videoControlsOverlay');
            this.seekBarCurrent = document.getElementById('seekBarCurrentProgress');
            this.fullscreenButton = document.getElementById('fullscreenButton');
            this.videoSeek = document.getElementById('seekBarBackground');
            this.volumeSeek = document.getElementById("volumeSeek");
            this.bottomLeftPlayPause = document.getElementById('bottomLeftPlayPause');
            this.volumeButton = document.getElementById('volumeButton');
            this.qualitiesDiv = document.getElementById('qualities');
            this.bottomControlBar = document.getElementById('bottomControlBar');
            this.menu = document.getElementById('menu');
            this.currentVolume = document.getElementById("currentVolume");
            this.timeStamp = document.getElementById('timeStamp');
            this.pictureInPicture = document.getElementById('pictureInPicture');
            this.settingsButton = document.getElementById('settingsButton');
            this.videoOverlay = document.getElementById('videoOverlay');
            this.speedsDiv = document.getElementById('speeds');
        }
    }

    initPlayer() {
        this.hls = new Hls();
        this.hls.attachMedia(this.videoElement);
        if (Hls.isSupported()) {
            this.hls.loadSource(this.playerState.srcURL);
        }
        this.hls.on(Hls.Events.MANIFEST_PARSED, () => {
            this.videoElement.addEventListener('loadeddata', () => {
                this.playerState.duration = this.videoElement.duration.toFixed(2);
                this.initPlay.classList.remove('hidden');
                this.loader.classList.add('hidden');
                this.videoThumbnailOverlay.addEventListener('click', () => this.initiatePlay());
            });
        });
    }

    initiatePlay() {
        this.videoThumbnailOverlay.classList.replace('flex', 'hidden');
        if(this.playerState.vastURL)
            this.fetchVASTAd()
                .then(mediaFile => mediaFile ? this.showAd(mediaFile) : this.playVideo())
                .catch(() => this.playVideo());
        else 
            this.playVideo();
    }
    
    playVideo() {
        this.adVideo.remove();
        this.attachControls();
        this.videoElement.style.display = 'block';
        this.togglePlay(true);
    }

    showAd(mediaFile) {
        this.videoElement.style.display = 'none';
        this.adVideo.style.display = 'block';
        this.videoElement.pause();
        this.adVideo.src = mediaFile;
        this.adVideo.play();
        this.adVideo.addEventListener('ended', () => this.playVideo(), { once: true });
    }

    attachControls() {
        this.videoControlsOverlay.classList.replace('hidden', 'flex');
        this.playerState.availableResolutions = this.hls.levels;
        const createQualityButton = (text) => {
            const button = document.createElement('button');
            button.textContent = text;
            button.className = 'w-full text-left px-2';
            button.onclick = () => this.setQuality(text);
            this.qualitiesDiv.appendChild(button);
        };
        this.playerState.availableResolutions.forEach((level, index) => createQualityButton(`${level.height}p`));
        createQualityButton('Auto');
        //this.setQuality(this.playerState.currentResolution);

        this.videoElement.addEventListener('timeupdate', ()=>{
            this.playerState.currentTime = Math.round(this.videoElement.currentTime);            
            const progressPercent = (this.playerState.currentTime / this.playerState.duration) * 100;
            const loadedPercent = (this.videoElement.buffered.length > 0) 
                ? (this.videoElement.buffered.end(0) / this.playerState.duration) * 100 
                : 0;            
            this.seekBarCurrent.style.width = `${progressPercent}%`;
            this.timeStamp.innerText = `${formatSecondsToTime(this.playerState.currentTime)} / ${formatSecondsToTime(this.playerState.duration)}`;
        });

        this.settingsButton.addEventListener('click', () => this.toggleSettings());
        this.pictureInPicture.addEventListener('click', () => this.togglePiP());
        this.fullscreenButton.addEventListener('click', () => this.toggleFullScreen());
        this.volumeButton.addEventListener('click', () => this.toggleMute());
        this.bottomLeftPlayPause.addEventListener('click', () => this.togglePlay());
        this.videoOverlay.addEventListener('click', () => this.togglePlay());

        if(document.getElementById('speeds')){
            const speedsDiv = document.getElementById('speeds');
            this.playerState.playbackSpeeds.forEach(speed => this.createSpeedButton(speed));
        }
        this.setPlaybackSpeed(this.playerState.currentPlaybackSpeed);
        this.updateVolume(this.playerState.volume);
        
        this.volumeSeek.addEventListener("mousedown", (e) =>
            this.handleSeek(e, "mousemove", "mouseup", this.volumeSeek, (r) => this.updateVolume(r))
        );
        this.volumeSeek.addEventListener("touchstart", (e) =>
            this.handleSeek(e, "touchmove", "touchend", this.volumeSeek, (r) => this.updateVolume(r))
        );

        this.videoSeek.addEventListener("mousedown", (e) =>
            this.handleSeek(e, "mousemove", "mouseup", this.videoSeek, (r) => this.updateVideoTime(r))
        );
        this.videoSeek.addEventListener("touchstart", (e) =>
            this.handleSeek(e, "touchmove", "touchend", this.videoSeek, (r) => this.updateVideoTime(r))
        );
    }

    updateSeek = (clientX, seekElement, callback) => {
        const rect = seekElement.getBoundingClientRect();
        const offsetX = clientX - rect.left;
        const width = rect.width;
        const ratio = Math.min(Math.max(offsetX / width, 0), 1);
        callback(ratio);
    };

    handleSeek = (startEvent, moveEvent, endEvent, seekElement, updateCallback) => {
        const moveHandler = (e) =>
            this.updateSeek(e.clientX || e.touches[0].clientX, seekElement, updateCallback);
        const endHandler = () => {
            document.removeEventListener(moveEvent, moveHandler);
            document.removeEventListener(endEvent, endHandler);
        };
        this.updateSeek(startEvent.clientX || startEvent.touches[0].clientX, seekElement, updateCallback);
        document.addEventListener(moveEvent, moveHandler);
        document.addEventListener(endEvent, endHandler);
    };

    updateVolume (ratio) {
        const volume = ratio;
        this.playerState.volume = volume;
        this.currentVolume.style.width = `${Math.round(volume * 100)}%`;
        this.videoElement.volume = volume;
    };

    updateVideoTime (ratio) {
        const currentTime = ratio * this.playerState.duration;
        this.playerState.currentTime = Math.round(currentTime);
        this.seekBarCurrent.style.width = `${Math.round(ratio * 100)}%`;
        this.videoElement.currentTime = currentTime;
    };

    async fetchVASTAd() {
        const response = await fetch(this.playerState.vastURL);
        const vastXML = await response.text();
        return this.parseVASTAd(vastXML);
    }

    parseVASTAd(vastXML) {
        const xmlDoc = new DOMParser().parseFromString(vastXML, 'application/xml');
        return xmlDoc.querySelector('MediaFile')?.textContent || null;
    }

    togglePlay(force = null) {
        if (typeof force === "boolean") {
            this.playerState.playing = force;
        } else {
            this.playerState.playing = !this.playerState.playing;
        }
        this.playerState.playing ? this.videoElement.play() : this.videoElement.pause();
        this.bottomLeftPlayPause.classList.replace(
            this.playerState.playing ? 'fa-play' : 'fa-pause', 
            this.playerState.playing ? 'fa-pause' : 'fa-play'
        );
        
        const showControls = () => {
            if (!this.playerState.throttleTimeout) {
                this.bottomControlBar.classList.remove('hidden');
                clearTimeout(this.playerState.mouseMoveTimeout);
                this.playerState.throttleTimeout = true;
                setTimeout(() => {this.playerState.throttleTimeout = false;}, 200);
                this.playerState.mouseMoveTimeout = setTimeout(() => {this.bottomControlBar.classList.add('hidden');}, 4000);
            }
        };

        if (this.playerState.playing){
            this.menu.classList.add('hidden');
            this.playerWrapper.addEventListener('mousemove', showControls);
        } else {
            this.playerWrapper.removeEventListener('mousemove', showControls);
            this.bottomControlBar.classList.remove('hidden');
        }
    }

    toggleSettings() {
        this.menu.classList.toggle('hidden');
    }

    async togglePiP() {
        if (this.videoElement !== document.pictureInPictureElement) {
            await this.videoElement.requestPictureInPicture();
            this.playerState.pictureInPicture = true;
            this.videoElement.addEventListener('leavepictureinpicture', () => {
                this.playerState.pictureInPicture = false;
            });
        } else {
            await document.exitPictureInPicture();
        }
    }

    setQuality(quality){
        const currentTime = this.videoElement.currentTime;
        const index = this.playerState.availableResolutions.findIndex(resolution => `${resolution.height}p` === quality);
        this.playerState.currentResolution = index;
        this.hls.currentLevel = index;
        const qualityButtons = this.qualitiesDiv.querySelectorAll('button');
        qualityButtons.forEach((button, buttonIndex) => {
            if (buttonIndex === index || (index === -1 && button.textContent === 'Auto')) {
                button.style.backgroundColor = this.playerState.menuActive;
                button.style.color = this.playerState.menuActiveText;
            } else {
                button.style.backgroundColor = this.playerState.menuBackground;
                button.style.color = this.playerState.menuText;
            }
        });
        this.menu.classList.add('hidden');
    }

    setPlaybackSpeed (speed) {
        const speedButtons = this.speedsDiv.querySelectorAll('button');
        speedButtons.forEach((button)=>{
            if(button.textContent === `${speed}x`){
                button.style.backgroundColor = this.playerState.menuActive;
                button.style.color = this.playerState.menuActiveText;
            } else{
                button.style.backgroundColor = this.playerState.menuBackground;
                button.style.color = this.playerState.menuText;
            }
        });
        this.playerState.currentPlaybackSpeed = speed;
        this.videoElement.playbackRate = speed;
        this.menu.classList.add('hidden');
    }
    
    createSpeedButton (speed) {
            const button = document.createElement('button');
            button.textContent = `${speed}x`;
            button.className = 'w-full text-left px-2';
            button.onclick = () => this.setPlaybackSpeed(speed);
            this.speedsDiv.appendChild(button);
        };
    
    toggleFullScreen() {
        this.playerState.fullscreen = !this.playerState.fullscreen;
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

        if (this.playerState.fullscreen && document.fullscreenElement !== playerWrapper) {
            enterFullscreen();
            this.fullscreenButton.classList.replace('fa-expand', 'fa-compress');
        } else if (!this.playerState.fullscreen && document.fullscreenElement === playerWrapper) {
            exitFullscreen();
            this.fullscreenButton.classList.replace('fa-compress', 'fa-expand');
        }
    }

    toggleMute() {
        this.playerState.muted = !this.playerState.muted;
        this.videoElement.muted = this.playerState.muted;
        this.volumeButton.classList.toggle('fa-volume-xmark', this.playerState.muted);
        this.volumeButton.classList.toggle('fa-volume-low', !this.playerState.muted);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const player = new VideoPlayer(playerState);
});
