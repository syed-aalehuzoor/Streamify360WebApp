<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\Video;

class PlayerController extends Controller
{
 
    
    public function player($id)
    {
        $video = Video::findOrFail($id);
        if ($video && $video->status === 'live') {
            $video->increment('views');
            $settings = UserSetting::where('user_id', $video->userid)->first();
            if (!$settings) {
                $settings = UserSetting::create(['user_id' => $video->userid]);
            }           
            return view('player', compact('video', 'settings'));
        }
        abort(404);
    }
}

