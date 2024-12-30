<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudienceInsight extends Model
{
    use HasFactory;
    // The table associated with the model
    protected $table = 'audience_insights';

    protected $fillable = [
        'videoid',
        'type',
        'name',
        'views',
        'percentage',
    ];

    // Define the relationship with the Video model
    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
