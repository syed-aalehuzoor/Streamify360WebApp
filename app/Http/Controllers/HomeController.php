<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
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
            'linux' => 'Linux',
            'android' => 'Android',
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

    public function test()
    {
        $user_ip = $this->get_client_ip();
        $geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
        $country = $geo["geoplugin_countryName"];
        $regionName = $geo["geoplugin_regionName"];
        $city = $geo["geoplugin_city"];
        $OSandDeviceType = $this->getOSandDeviceType();
        $deviceType = $OSandDeviceType['type'];
        $operatingSystem = $OSandDeviceType['os'];
        return view('test', compact('city','regionName', 'country', 'deviceType', 'operatingSystem'));
    }

    public function index()
    {
        if(Auth::user()->usertype=='admin')
        {
            return redirect('/admin');
        }
        else if(Auth::user()->usertype=='user'){
            return redirect('/dashboard');
        }
        abort(404);
        
    }

    
    public function dashboard(){
        $user = Auth::user();
        $totalViews = Video::where('userid', $user->id)->sum('views');
        $totalVideos = Video::where('userid', $user->id)->count();
        return view('dashboard', compact('user', 'totalViews', 'totalVideos'));
    }
}
