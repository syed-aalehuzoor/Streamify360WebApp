<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use App\Services\CloudflareService;
use App\Jobs\DomainActivationCheck;
use Illuminate\Support\Facades\DB;

class CustomDomainController extends Controller
{

    protected $cloudflareService;

    public function __construct()
    {
        $this->cloudflareService =  new CloudflareService();
    }

    public function edit()
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        if(!$settings->player_domain_varified){
            $zone = $this->cloudflareService->getZoneByDomainName($settings->player_domain);
            if ($zone && isset($zone['status']) && $zone['status'] === 'active') {
                $settings->player_domain_varified = true;
                $settings->save();
            }
        }
        return view('settings.edit-domain', [
            'user' => $user,
            'customDomain' => $settings->player_domain,
            'domainVarified' => $settings->player_domain_varified,
        ]);
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $newDomain = trim($request->input('player_domain'));
        $defaultDomain = config('system.playerDefaultDomain');
    
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id]
        );

        if (UserSetting::whereRaw('LOWER(player_domain) = ?', [strtolower($newDomain)])->exists()) {
            return back()->with('error', 'Domain already in use.');
        }
    
        if (empty($newDomain) || $newDomain === $defaultDomain) {
            $settings->update(['player_domain' => $defaultDomain, 'player_domain_varified' => true]);
        } else {
            if (!filter_var($newDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return back()->with('error', 'Invalid domain format.');
            }
    
            if (UserSetting::where('player_domain', $newDomain)->exists()) {
                return back()->with('error', 'Domain already in use.');
            }
    
            try {
                DB::transaction(function () use ($settings, $newDomain) {
                    $settings->update([
                        'player_domain' => $newDomain,
                        'player_domain_varified' => false
                    ]);
                    $this->cloudflareService->createZone($newDomain);
                    DomainActivationCheck::dispatch($newDomain)
                        ->delay(now()->addMinutes(5));
                });
            } catch (\Exception $e) {
                return back()->with('error', 'Domain update failed.');
            }
        }
    
        return back()->with('success', 'Settings updated successfully.');
    }
}
