<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use App\Services\CloudflareService;
use App\Jobs\DomainActivationCheck;
use Illuminate\Support\Facades\DB;

class CustomDomainController extends Controller
{

    protected $domainService;

    public function __construct()
    {
        $this->domainService =  new CloudflareService();
    }

    public function edit()
    {
        $user = Auth::user();
        $settings = UserSetting::firstOrCreate(['user_id' => $user->id]);
    
        $defaultDomain = config('system.playerDefaultDomain');
        $customDomain = $settings->player_domain ?: $defaultDomain;
        $nameservers = [];

        if (!$settings->player_domain_varified) {
            $zone = $this->domainService->getZoneByDomainName($settings->player_domain);
            if ($zone && isset($zone['status']) && $zone['status'] === 'active') {
                $settings->update(['player_domain_varified' => true]);
            }
            $nameservers = $zone['name_servers'];
        }
    
        return view('settings.edit-domain', [
            'user' => $user,
            'customDomain' => $customDomain,
            'nameservers' => $nameservers,
            'domainVarified' => $settings->player_domain_varified
        ]);        
    }
    
    function isValidDomain($domain) {
        return preg_match('/^(?!-)[A-Za-z0-9-]+(\.[A-Za-z]{2,})+$/', $domain);
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $newDomain = trim($request->input('domain'));
        $defaultDomain = config('system.playerDefaultDomain');
        $settings = UserSetting::where('user_id', $user->id)->first();
        
        $oldDomain = $settings->player_domain;
        if($oldDomain != null) $this->domainService->deleteZoneByDomainName($oldDomain);

        if (empty($newDomain) || $newDomain === $defaultDomain) {
            $settings->update([
                'player_domain' => null,
                'player_domain_varified' => true
            ]);
        } else if (!$this->isValidDomain($newDomain)) {
            return back()->with('error', 'Invalid domain format.');
        } else if (UserSetting::where(['player_domain'=> strtolower($newDomain)])->exists()) {
            return back()->with('error', 'Domain already in use.');
        } else {
            $settings->update([
                'player_domain' => $newDomain,
                'player_domain_varified' => false
            ]);
            $this->domainService->createZone($newDomain);
            DomainActivationCheck::dispatch($newDomain)
                ->delay(now()->addMinutes(5));
        }
        return back()->with('success', 'Settings updated successfully.');
    }
}