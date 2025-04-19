<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Viewer;
use App\Models\View;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use App\Models\Setting;

class PlayerController extends Controller
{
    public function show(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        if ($video && $video->status === 'live') {
            if ($video->publication_status == 'Banned') abort(404);
            if ($video->user->user_status == 'Banned') abort(404);
            $video->increment('views');
            $settings = $video->user->userSetting;
            if(!$video->user->hasPlan('premium'))
            {
                $vast_link = Setting::firstOrCreate(['key' => 'VAST_LINK'])->value;
                $pop_ads_code = Setting::firstOrCreate(['key' => 'POP_AD_CODE'])->value;
            } else {
                $vast_link = $settings->vast_link;
                $pop_ads_code = $settings->pop_ads_code;
            }
            $domain = $request->getHost();
            if ($domain !== config('system.playerDefaultDomain')){
                return ViewFacade::make('presentation.iframed', [
                    'name' => $video->name,
                    'websiteName' => $settings->website_name,
                    'logo' => $settings->logo_url,
                    'src' => 'https://'.config('system.playerDefaultDomain').'/video/'.$video->id,
                ]);                
            }
            $manifest_url = 'https://streambox.' . $video->server->domain . '/streams/' 
                            . $video->id . '/' . $video->manifest_url ;
            return ViewFacade::make('player.'.$settings->player, [
                'src' => $manifest_url,
                'id' => $video->id,
                'vastlink' => $vast_link,
                'popAdsCode' => $pop_ads_code,
                'name' => $video->name,
                'poster' => $video->thumbnail_url,

                'logo' => $settings->logo_url,
                'websiteURL' => $settings->website_url,
                'websiteName' => $settings->website_name,
                'playerBackground' => $settings->player_background,
                'seekBarBackground' => $settings->seek_bar_background,
                'seekBarLoadedProgress' => $settings->seek_bar_loaded_progress,
                'seekBarCurrentProgress' => $settings->seek_bar_current_progress,
                'controlButtons' => $settings->control_buttons,
                'thumbnailBackground' => $settings->thumbnail_background,
                'volumeSeek' => $settings->volume_seek,
                'volumeSeekBackground' => $settings->volume_seek_background,
                'menuBackground' => $settings->menu_background,
                'menuActive' => $settings->menu_active,
                'menuText' => $settings->menu_text,
                'menuActiveText' => $settings->menu_active_text,
                'customPlaybackSpeeds' => $settings->playback_speeds,
                'playbackSpeed' => $settings->default_playback_speed,
                'volume' => $settings->default_volume,
                'showPlaybackSpeed' => $settings->show_playback_speed,
                'muted' => $settings->default_muted,
                'loop' => $settings->loop,
                'controls' => $settings->controls,
            ]);
        }
        abort(404);
    }
}
