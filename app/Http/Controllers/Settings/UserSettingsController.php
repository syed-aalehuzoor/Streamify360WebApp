<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSetting;

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
        $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
        switch ($id)
        {
            case 'player':
                return view('settings.player', compact('settings'));
            case 'ads':
                return view('settings.ads', compact('settings'));
            case 'custom-domain':
                return view('settings.custom-domain', compact('settings'));
            default:
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
        $settings->update($data);
        return redirect()->back()->with('success', "Video updated successfully.");
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
