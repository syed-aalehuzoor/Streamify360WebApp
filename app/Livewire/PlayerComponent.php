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
        return view('livewire.player-component', [
            'video' => $this->video,
            'settings' => $this->settings,
        ]);
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
