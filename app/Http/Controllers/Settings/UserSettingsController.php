<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;
use App\Jobs\DomainActivationCheck;
use App\Services\CloudflareService;

class UserSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $settings = $user->settings;
        switch ($id)
        {
            case 'player':
                return view('settings.player', compact('settings'));
            case 'ads':
                return view('settings.ads', compact('settings'));
            case 'custom-domain':
                if(!$settings->player_domain_varified){
                    $cloudflareService = new CloudflareService();
                    $zone = $cloudflareService->getZoneByDomainName($settings->player_domain);
                    if ($zone && isset($zone['status']) && $zone['status'] === 'active'){
                        $cloudflareService->deleteAllDnsRecords($settings->player_domain);
                        $cloudflareService->createARecord($settings->player_domain, config('system.playerServerIP'));
                        $settings->update(['player_domain_varified' => true]);
                    }
                }
                return view('settings.custom-domain', compact('settings'));
            default:
                dd('Invalid settings type.');
                abort(404);
        }
    }

        /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
        $data = $request->except(['_token', '_method']);
        if ($request->has('player_domain')) {
            $playerDomain = $request->input('player_domain');
            if ($playerDomain !== $settings->player_domain) {
                if ($playerDomain === null || $playerDomain == config('system.playerDefaultDomain')) {
                    $data['player_domain'] = config('system.playerDefaultDomain');
                    $data['player_domain_varified'] = true;
                } else {
                    $data['player_domain'] = $playerDomain;
                    $data['player_domain_varified'] = false;
                    DomainActivationCheck::dispatch($playerDomain);
                }
            }
        }
        if ($request->has('logo_file')){
            $old_logopath = public_path('uploads/' . $settings->logo_url);
            if (file_exists($old_logopath) && is_file($old_logopath)) {
                unlink($old_logopath);
            }
            $file = $request->file('logo_file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('brands'), $filename);        
            $settings->update(['logo_url' => asset('brands/' . $filename)]);
        }
        $settings->update($data);
        return redirect()->back()->with('success', "Settings updated successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return $this->show($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
