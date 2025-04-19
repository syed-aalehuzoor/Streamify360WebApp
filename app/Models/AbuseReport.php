<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbuseReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'reason',
        'details',
        'status',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
