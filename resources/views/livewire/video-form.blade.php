<div id="upload-page" x-data="{ isUploading: false, progress: 0 }">

    @if ($currentStep === 1)
        <div x-data="{ activeTab: 1 }">
            <div class="flex justify-around flex-wrap border-b border-gray-300 mb-4">
                <x-tab tabId="1" tabName="Upload" icon="fa-solid fa-upload" />
                <x-tab tabId="2" tabName="YouTube URL" icon="fa-brands fa-youtube" />
                <x-tab tabId="3" tabName="Drive URL" icon="fa-brands fa-google-drive" />
                <x-tab tabId="4" tabName="Direct URL" icon="fa-solid fa-link" />
            </div>
        
            <div x-show="activeTab === 1">
                <form wire:submit.prevent="uploadVideo" enctype="multipart/form-data" class="flex flex-col gap-4">        
                    <div>
                        <label for="video" class="block text-sm font-medium text-gray-700">Video File (required):</label>
                        <input onchange="uploadChunks('video')" type="file" id="video" class="mt-1 block w-full px-3 py-8 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required
                            x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">
                        @error('video') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="upload-container" class="mt-6 p-4 border border-gray-300 rounded-lg bg-white shadow-lg" style="display: none;">
                        <!-- Progress Bar -->
                        <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                            <div id="progress-bar" class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300" style="width: 0%;">
                            </div>
                        </div>

                        <!-- Uploading Text and Percentage -->
                        <div class="flex justify-between items-center mt-3">
                            <div class="text-sm text-gray-700 font-semibold animate-pulse">
                                Uploading...
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div x-show="activeTab === 2">
                <form wire:submit.prevent="submitYouTubeUrl" class="flex flex-col gap-4">
                    <div>
                        <label for="youtubeUrl" class="block text-sm font-medium text-gray-700">YouTube Video URL:</label>
                        <input type="url" wire:model="youtubeUrl" id="youtubeUrl" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('youtubeUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
        
                    <div class="flex justify-end mt-4">
                        <x-button type="submit">Submit YouTube URL</x-button>
                    </div>
                </form>
            </div>
        
            <div x-show="activeTab === 3">
                <form wire:submit.prevent="submitDriveUrl" class="flex flex-col gap-4">
                    <div>
                        <label for="driveUrl" class="block text-sm font-medium text-gray-700">Google Drive Video URL:</label>
                        <input type="url" wire:model="driveUrl" id="driveUrl" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('driveUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
        
                    <div class="flex justify-end mt-4">
                        <x-button type="submit">Submit Drive URL</x-button>
                    </div>
                </form>
            </div>
        
            <div x-show="activeTab === 4">
                <form wire:submit.prevent="submitDirectUrl" class="flex flex-col gap-4">
                    <div>
                        <label for="directUrl" class="block text-sm font-medium text-gray-700">Direct Video URL:</label>
                        <input type="url" wire:model="directUrl" id="directUrl" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        @error('directUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
        
                    <div class="flex justify-end mt-4">
                        <x-button type="submit">Submit Direct URL</x-button>
                    </div>
                </form>
            </div>
        </div>

    @elseif ($currentStep === 2)
        <!-- Metadata, Logo, Subtitle, Thumbnail Section -->
        <form wire:submit.prevent="submitVideoMetaAndOthers" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf
            <!-- Video Metadata -->
            <div>
                <label for="videoname" class="block text-sm font-medium text-gray-700">Video Name (required):</label>
                <input type="text" wire:model="videoname" id="videoname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('videoname') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Logo Upload with Progress -->
            <div x-data="{ isUploadingLogo: false, progressLogo: 0 }">

                <label for="logo" class="block text-sm font-medium text-gray-700">Logo (optional):</label>
                <input type="file" wire:model="logo" id="logo" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" @if (!in_array($user->userplan, config('features.logo_hardcoding'))) disabled title="Upgrade plan to get this Feature." @endif
                    x-on:livewire-upload-start="isUploadingLogo = true"
                    x-on:livewire-upload-finish="isUploadingLogo = false"
                    x-on:livewire-upload-error="isUploadingLogo = false"
                    x-on:livewire-upload-progress="progressLogo = $event.detail.progress">
                @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <!-- Progress Bar for Logo -->
                <div x-show="isUploadingLogo" class="mt-2">
                    <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300" :style="{ width: progressLogo + '%' }"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1 text-xs text-gray-600">
                        <div class="font-semibold">Uploading logo...</div>
                        <div class="font-medium"><span x-text="progressLogo"></span>%</div>
                    </div>
                </div>
            </div>

            <!-- Subtitle Upload with Progress -->
            <div x-data="{ isUploadingSubtitle: false, progressSubtitle: 0 }">
                <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtitle (optional):</label>
                <input type="file" accept=".ass" wire:model="subtitle" id="subtitle" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" @if (!in_array($user->userplan, config('features.subtitle_hardcoding'))) disabled title="Upgrade plan to get this Feature." @endif
                    x-on:livewire-upload-start="isUploadingSubtitle = true"
                    x-on:livewire-upload-finish="isUploadingSubtitle = false"
                    x-on:livewire-upload-error="isUploadingSubtitle = false"
                    x-on:livewire-upload-progress="progressSubtitle = $event.detail.progress">
                @error('subtitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <!-- Progress Bar for Subtitle -->
                <div x-show="isUploadingSubtitle" class="mt-2">
                    <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300" :style="{ width: progressSubtitle + '%' }"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1 text-xs text-gray-600">
                        <div class="font-semibold">Uploading subtitle...</div>
                        <div class="font-medium"><span x-text="progressSubtitle"></span>%</div>
                    </div>
                </div>
            </div>

            <!-- Thumbnail Upload with Progress -->
            <div x-data="{ isUploadingThumbnail: false, progressThumbnail: 0 }">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail (optional):</label>
                <input type="file" wire:model="thumbnail" id="thumbnail" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    x-on:livewire-upload-start="isUploadingThumbnail = true"
                    x-on:livewire-upload-finish="isUploadingThumbnail = false"
                    x-on:livewire-upload-error="isUploadingThumbnail = false"
                    x-on:livewire-upload-progress="progressThumbnail = $event.detail.progress">
                @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                <!-- Progress Bar for Thumbnail -->
                <div x-show="isUploadingThumbnail" class="mt-2">
                    <div class="relative h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full bg-blue-500 transition-all duration-300" :style="{ width: progressThumbnail + '%' }"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1 text-xs text-gray-600">
                        <div class="font-semibold">Uploading thumbnail...</div>
                        <div class="font-medium"><span x-text="progressThumbnail"></span>%</div>
                    </div>
                </div>
            </div>

            <!-- Publish Button -->
            <div class="flex justify-end mt-4">
                <x-button type="submit">Publish</x-button>
            </div>
        </form>
    @endif

</div>
<script>

function updateProgress(progress) {
    document.getElementById('progress-bar').style.width = `${progress}%`;
}

function livewireUploadChunk( file, start ){
    const now = new Date();
    console.log(`${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}.${now.getMilliseconds()}`);
    const chunkEnd  = Math.min( start + @js($chunkSize), file.size );
    const chunk     = file.slice( start, chunkEnd ); 
    const totalProgress = Math.round((chunkEnd / file.size) * 100);
    @this.upload('fileChunk', chunk,(uName)=>{}, ()=>{}, (event)=>{
        if( event.detail.progress == 100 ){
          updateProgress(totalProgress);
          start = chunkEnd;
          if( start < file.size ){
            livewireUploadChunk( file, start );
          }
        }
    });
}

function uploadChunks(id){
    const file = document.querySelector(`#${id}`).files[0];
    const randomString = Math.random().toString(36).substring(2, 15);
    const fileExtension = file.name.split('.').pop().toLowerCase();
    // Send the following later at the next available call to component
    @this.set('fileName', file.name, true);
    @this.set('fileKey', randomString, true);
    @this.set('fileExtension', fileExtension, true);
    @this.set('fileSize', file.size, true);
    document.getElementById('upload-container').style.display = 'block';
    livewireUploadChunk( file, 0 );
}
</script>