<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class Settings extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mailSettings = [
            'MAIL_MAILER' => Setting::firstOrCreate(['key' => 'MAIL_MAILER']),
            'MAIL_HOST' => Setting::firstOrCreate(['key' => 'MAIL_HOST']),
            'MAIL_PORT' => Setting::firstOrCreate(['key' => 'MAIL_PORT']),
            'MAIL_USERNAME' => Setting::firstOrCreate(['key' => 'MAIL_USERNAME']),
            'MAIL_PASSWORD' => Setting::firstOrCreate(['key' => 'MAIL_PASSWORD']),
            'MAIL_ENCRYPTION' => Setting::firstOrCreate(['key' => 'MAIL_ENCRYPTION']),
            'MAIL_FROM_ADDRESS' => Setting::firstOrCreate(['key' => 'MAIL_FROM_ADDRESS']),
            'MAIL_FROM_NAME' => Setting::firstOrCreate(['key' => 'MAIL_FROM_NAME']),
        ];
        $adsSettings = [
            'POP_AD_CODE' => Setting::firstOrCreate(['key' => 'POP_AD_CODE']),
            'VAST_LINK' => Setting::firstOrCreate(['key' => 'VAST_LINK']),
        ];
        return view('admin.settings.index', compact('mailSettings', 'adsSettings'));
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
        $validatedData = $request->validate([
            'MAIL_MAILER' => 'required|string',
            'MAIL_HOST' => 'required|string',
            'MAIL_PORT' => 'required|integer',
            'MAIL_USERNAME' => 'required|string',
            'MAIL_PASSWORD' => 'required|string',
            'MAIL_ENCRYPTION' => 'required|string',
            'MAIL_FROM_ADDRESS' => 'required|email',
            'MAIL_FROM_NAME' => 'required|string',
            'POP_AD_CODE' => 'nullable|string',
            'VAST_LINK' => 'nullable|string',
        ]);
        $validatedData = Arr::except($validatedData, ['_token', '_method', 'submit', 'save']);
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
