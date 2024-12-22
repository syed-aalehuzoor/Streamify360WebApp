<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewsOverTime extends Model
{
    use HasFactory;

    protected $table = 'views_over_time';

    protected $fillable = [
        'videoid',
        'date',
        'views',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
