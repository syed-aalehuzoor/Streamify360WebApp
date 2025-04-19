<div id="upload-page" x-data="{ isUploading: false, progress: 0 }">
    @if ($currentStep === 1)
        <div x-data="{ activeTab: 1 }">
            <div class="flex flex-wrap mb-4">
                <x-tab tabId="1" tabName="Upload" icon="fa-solid fa-upload" />
                <x-tab tabId="2" tabName="YouTube URL" icon="fa-brands fa-youtube" />
                <x-tab tabId="3" tabName="Drive URL" icon="fa-brands fa-google-drive" />
                <x-tab tabId="4" tabName="Direct URL" icon="fa-solid fa-link" />
            </div>

            <div class="p-6">
                <div x-show="activeTab === 1" class="flex flex-col gap-4">
                    <label for="video-upload-wrapper" class="block text-sm font-medium text-gray-700">Video File:</label>
                    <div id="video-upload-wrapper"></div>
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

        </div>

    @elseif ($currentStep === 2)
        <!-- Metadata, Logo, Subtitle, Thumbnail Section -->
        <form wire:submit.prevent="submitVideoMetaAndOthers" enctype="multipart/form-data" class="flex flex-col p-6 gap-4">
            @csrf
            <!-- Video Metadata -->
            <div>
                <label for="videoname" class="block text-sm font-medium text-gray-700">Video Name (required):</label>
                <input type="text" wire:model="videoname" id="videoname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('videoname') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
    document.addEventListener('DOMContentLoaded', () => {
        new FileUploader('video', 'video-upload-wrapper', '.mp4', 1024 * 1024 * 10, (uploadId) => {
            @this.set('uploadId', uploadId, true);
        });
    });
</script>