<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Video;

class VideoStatus extends Component
{
    public $videoId;
    public $status;

    protected $listeners = ['refreshStatus' => '$refresh'];

    public function mount($videoId)
    {
        $this->videoId = $videoId;
        $this->status = Video::find($this->videoId)->status;
    }

    public function updateStatus()
    {
        $video = Video::find($this->videoId);

            $this->status = $video->status;
    }

    public function render()
    {
        return view('livewire.video-status');

    }
}
