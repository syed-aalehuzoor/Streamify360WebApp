<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ChunkedFileInput extends Component
{
    use WithFileUploads;

    public $filename;
    public $acceptedTypes;
    public $uploadId;

    public function updateduploadId()
    {
        dd($this->uploadId);
        return redirect('/home');
    }

    public function render()
    {
        return view('livewire.chunked-file-input');
    }

}
