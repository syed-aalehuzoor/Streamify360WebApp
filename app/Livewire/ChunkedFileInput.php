<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ChunkedFileInput extends Component
{
    use WithFileUploads;

    public $filename;
    public $acceptedTypes;
    public $file;

    public function render()
    {
        return view('livewire.chunked-file-input');
    }

}
