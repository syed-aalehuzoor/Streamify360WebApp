<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Viewer;
use App\Models\View;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;

class PlayerController extends Controller
{
    public function show(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        if ($video && $video->status === 'live') {
            $viewer = $this->getOrRegisterViewer($request);
            View::create([
                'videoid' => $video->id,
                'viewerid' => $viewer->id,
                'country' => $viewer->country,
                'region' => $viewer->region,
                'city' => $viewer->city,
                'device_type' => $viewer->device_type,
            ]);

            $video->increment('views');

            $settings = UserSetting::where('user_id', $video->userid)->first();

            if (!$settings) {
                $settings = UserSetting::create(['user_id' => $video->userid]);
            }

            return ViewFacade::make('presentation.player', [
                'src' => $video->manifest_url,
                'vastlink' => $settings->vast_link,
                'popAdsCode' => $settings->pop_ads_code,
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

    private function getOrRegisterViewer(Request $request)
    {
        $userIp = $this->getClientIp($request);
        $viewer = Viewer::where('ip', $userIp)->first();

        if (!$viewer) {
            $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$userIp"));
            $country = $geo['geoplugin_countryName'] ?? null;
            $region = $geo['geoplugin_regionName'] ?? null;
            $city = $geo['geoplugin_city'] ?? null;
            $osAndDeviceType = $this->getOsAndDeviceType($request);

            $viewer = Viewer::create([
                'ip' => $userIp,
                'country' => $country,
                'region' => $region,
                'city' => $city,
                'device_type' => $osAndDeviceType['type'],
                'operating_system' => $osAndDeviceType['os'],
                'error_reports' => null,
                'frequency_of_visits' => 1,
                'first_visit' => now(),
                'last_visit' => now(),
            ]);
        }

        return $viewer;
    }

    private function getClientIp(Request $request)
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            $ip = $request->server($key);
            if ($ip && strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        return 'UNKNOWN';
    }

    private function getOsAndDeviceType(Request $request)
    {
        $userAgent = $request->server('HTTP_USER_AGENT');
        $osArray = [
            'windows' => 'Windows',
            'macintosh' => 'Mac OS',
            'android' => 'Android',
            'linux' => 'Linux',
            'iphone' => 'iOS',
            'ipad' => 'iOS',
        ];

        $deviceArray = [
            'mobile' => "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\\.browser|up\\.link|webos|wos)/i",
            'tablet' => "/(tablet|ipad|playbook|silk)/i",
            'desktop' => "/(windows|macintosh|linux|x11)/i",
        ];

        $os = 'Unknown';
        foreach ($osArray as $key => $value) {
            if (stripos($userAgent, $key) !== false) {
                $os = $value;
                break;
            }
        }

        $deviceType = 'Unknown';
        foreach ($deviceArray as $type => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $deviceType = ucfirst($type);
                break;
            }
        }

        return ['type' => $deviceType, 'os' => $os];
    }
}
