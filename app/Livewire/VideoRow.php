<?php

namespace App\Livewire;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

use Livewire\Component;

class VideoRow extends Component
{
    public $video;

    public function updatevideo()
    {
        $this->video = $this->getUserVideo($this->video->id);
    }

    public function render()
    {
        return view('livewire.video-row');
    }

    /**
     * Get video for the authenticated user
     * 
     * @param int $id
     * @return Video|null
     */
    private function getUserVideo($id) {
        return Video::where('id', $id)
            ->where('userid', Auth::id())
            ->first();
    }
}
