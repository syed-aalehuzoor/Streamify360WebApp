<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessVideo;

class VideoForm extends Component
{
    use WithFileUploads;

    public $video_id;
    public $currentStep = 1;

    public $youtubeUrl;
    public $driveUrl;
    public $directUrl;

    public $videoname;
    public $logo;
    public $subtitle;
    public $thumbnail;

    public $uploadId;
    
    public function updateduploadId()
    {
        $finalFile = collect(Storage::files('chunks'))
            ->first(fn($file) => str_starts_with($file, "chunks/{$this->uploadId}."));

        // Create video and upload to Azure
        $extension = pathinfo(Storage::path($finalFile), PATHINFO_EXTENSION);
        $video = $this->createVideo("Untitled", 'pending');
        $azurePath = "videos/{$video->id}.{$extension}";
        
        Storage::disk('azure')->put($azurePath, Storage::get($finalFile));
        $video->update(['video_url' => $azurePath]);
    }

    public function render(){
        $user = Auth::user();
        return view('livewire.video-form', compact('user'));
    }

    private function createVideo($name, $videoUrl)
    {
        $video = Video::create([
            'name' => $name,
            'userid' => Auth::id(),
            'serverid' => $this->getServerId(),
            'status' => 'Draft',
            'video_url' => $videoUrl,
        ]);
        $this->video_id = $video->id;
        $this->currentStep = 2;
        return $video;
    }
    
    public function submitYouTubeUrl() {
        $this->validate(['youtubeUrl' => 'required|url']);
        $this->createVideo('Untitled Video from YouTube', $this->youtubeUrl);
    }
    
    public function submitDriveUrl() {
        $this->validate(['driveUrl' => 'required|url']);
        $this->createVideo('Untitled Video From Google Drive', $this->driveUrl);
    }
    
    public function submitDirectUrl() {
        $this->validate(['directUrl' => 'required|url']);
        $this->createVideo('Untitled Video', $this->directUrl);
    }

    public function mount()
    {
        if (request()->has('video_id')) {
            $video_id = (string) request()->get('video_id');
            $this->video_id = $video_id;
            $this->currentStep = 2;
        }
    }

    private function handleEnterpriseUploads(Video $video){
        if ($this->logo) {
            $this->validate(['logo' => 'image|max:1024']);
            $logoPath = $this->logo->storeAs(
                'logos',
                $this->video_id . '.' . $this->logo->getClientOriginalExtension(),
                'azure'
            );
            $video->update(['logo_url' => $logoPath]);
        }
        if ($this->subtitle) {
            $this->validate(['subtitle' => 'file|max:2048']);
            $subtitlePath = $this->subtitle->storeAs(
                'subtitles',
                $this->video_id . '.' . $this->subtitle->getClientOriginalExtension(),
                'azure'
            );
            $video->update(['subtitle_url' => $subtitlePath]);
        }
    }

    public function submitVideoMetaAndOthers() {
        $this->validate(['videoname' => 'required|string|max:255']);
        $user = Auth::user();
        $video = Video::where('id', $this->video_id)->where('userid', $user->id)->first();
        $video->update([
            'name' => $this->videoname,
            'manifest_url' => 'https://streambox.streamify360.net/streams/'.$this->video_id.'/master.m3u8'
        ]);
        if ($user->userplan == 'enterprise'){
            $this->handleEnterpriseUploads($video);
        }
        if ($this->thumbnail) {
            $this->validate(['thumbnail' => 'image|max:1024']);
            $thumbnailPath = $this->thumbnail->storeAs('thumbnails', $this->video_id . '.' . $this->thumbnail->getClientOriginalExtension(), 'public');
            $video->update(['thumbnail_url' => $thumbnailPath]);
        }
        ProcessVideo::dispatch($this->video_id);
        $video->update(['status' => 'Initiated']);
        return redirect()->route('videos.index')->with('success', 'Video uploaded is being processed!');
    }

    private function getServerId(){
        return 1;
    }
}
