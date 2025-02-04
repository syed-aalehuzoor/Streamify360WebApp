<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Video;
use App\Models\Viewer;
use App\Models\View;
use App\Models\UserSetting;

class PlayerComponent extends Component
{
    public $video;
    public $settings;

    public function mount($id)
    {
        $this->video = Video::findOrFail($id);

        if ($this->video && $this->video->status === 'live') {
            $this->video->increment('views');
            $this->settings = UserSetting::firstOrCreate(
                ['user_id' => $this->video->userid]
            );
        } else {
            abort(404);
        }
    }

    public function render()
    {
        if($this->video->status == 'live')
        {
            $settings = $this->settings;
            $video = $this->video;
            $playerConfig = [
                'responsive' => $settings['responsive'],
                'playerWidth' => $settings['player_width'] ?? '100%',
                'playerHeight' => $settings['player_height'] ?? '100%',
                'autoplay' => $settings['autoplay'],
                'showControls' => $settings['show_controls'],
                'playbackRateOptions' => $settings['show_playback_speed'] ? [0.5, 1, 1.5, 2] : [],
                'defaultPlaybackSpeed' => str_replace('x', '', $settings['playback_speed']),
                'thumbnailUrl' => $video->thumbnail_url,
                'manifestUrl' => $video->manifest_url,
                'volume' => $settings['volume_level'],
                'vastLink' => $settings['vast_link'] ?? '',
                'colors' => [
                    'controlbar' => [
                        'background' => $settings['controlbar_background_color'] ?? '#000000b3',
                        'icons' => $settings['controlbar_icons_color'] ?? '#FFFFFF',
                        'iconsActive' => $settings['controlbar_icons_active_color'] ?? '#FF0000',
                        'text' => $settings['controlbar_text_color'] ?? '#FFFFFF',
                    ],
                    'menus' => [
                        'background' => $settings['menu_background_color'] ?? '#333333',
                        'text' => $settings['menu_text_color'] ?? '#FFFFFF',
                        'textActive' => $settings['menu_text_active_color'] ?? '#FF0000',
                    ],
                    'timeslider' => [
                        'progress' => $settings['timeslider_progress_color'] ?? '#FF0000',
                        'rail' => $settings['timeslider_rail_color'] ?? '#FFFFFF',
                    ],
                    'tooltips' => [
                        'background' => $settings['tooltip_background_color'] ?? '#000000',
                        'text' => $settings['tooltip_text_color'] ?? '#FFFFFF',
                    ],
                ],
            ];
            return view(
                'livewire.player-component',
                [
                    'video' => $this->video,
                    'settings' => $this->settings,
                    'playerConfig' => $playerConfig,
                ]
            );
        }
        else {
            abort(404);
        }
    }

    public function rendered()
    {
        $viewer = $this->get_or_register_viewer();
        View::create([
            'videoid' => $this->video->id,
            'viewerid' => $viewer->id,
            'country' => $viewer->country,
            'region' => $viewer->region,
            'city' => $viewer->city,
            'device_type' => $viewer->device_type,
        ]);
    }

    function get_client_ip() {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) $ip = explode(',', $ip)[0];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $ip;
            }
        }
        return 'UNKNOWN';
    }

    function getOSandDeviceType() {
        $userAgent = $_SERVER["HTTP_USER_AGENT"];    
        $osArray = [
            'windows' => 'Windows',
            'macintosh' => 'Mac OS',
            'android' => 'Android',
            'linux' => 'Linux',
            'iphone' => 'iOS',
            'ipad' => 'iOS',
        ];
    
        $deviceArray = [
            'mobile' => "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            'tablet' => "/(tablet|ipad|playbook|silk)/i",
            'desktop' => "/(windows|macintosh|linux|x11)/i",
        ];
    
        $os = "Unknown";
        foreach ($osArray as $key => $value) {
            if (stripos($userAgent, $key) !== false) {
                $os = $value;
                break;
            }
        }
    
        $deviceType = "Unknown";
        foreach ($deviceArray as $type => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $deviceType = ucfirst($type);
                break;
            }
        }
    
        return ["type" => $deviceType, "os" => $os];
    }

    function get_or_register_viewer()
    {
        $user_ip = $this->get_client_ip();
        $viewer = Viewer::where('ip', $user_ip)->first();

        if (!$viewer) {
            $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
            $country = $geo["geoplugin_countryName"] ?? null;
            $region = $geo["geoplugin_regionName"] ?? null;
            $city = $geo["geoplugin_city"] ?? null;
            $OSandDeviceType = $this->getOSandDeviceType();
            $deviceType = $OSandDeviceType['type'];
            $operatingSystem = $OSandDeviceType['os'];

            $viewer = Viewer::create([
                'ip' => $user_ip,
                'country' => $country,
                'region' => $region,
                'city' => $city,
                'device_type' => $deviceType,
                'operating_system' => $operatingSystem,
                'error_reports' => null,
                'frequency_of_visits' => 1,
                'first_visit' => now(),
                'last_visit' => now(),
            ]);
        }
    
        return $viewer;
    }
}
