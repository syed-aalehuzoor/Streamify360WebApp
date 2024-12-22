<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;
    protected $fillable = [
        'videoid',
        'viewerid',
        'country',
        'region',
        'city',
        'device_type',
    ];

    /**
     * Get the viewer that owns the view.
     */
    public function viewer()
    {
        return $this->belongsTo(Viewer::class, 'viewerid');
    }
}
