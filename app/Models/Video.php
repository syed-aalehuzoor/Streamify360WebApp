<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    public $incrementing = false;  // Disable auto-incrementing since the id will be a string

    protected $keyType = 'string'; // Specify that the primary key is a string

    protected $fillable = [
        'name',
        'userid',
        'serverid',
        'status',
        'manifest_url',
        'video_url',
        'thumbnail_url',
        'subtitle_url',
        'logo_url',
    ];

    protected static function boot()
    {
        parent::boot();

        // Hook into the creating event to generate the ID
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::random(11); // Generate a random 11-character string
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
