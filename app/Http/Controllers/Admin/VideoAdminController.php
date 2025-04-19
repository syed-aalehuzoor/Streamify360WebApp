<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\User;
use App\Models\AbuseReport;

class VideoAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
    
        if ($query) {
            $videos = Video::where('name', 'like', '%' . $query . '%')
                           ->orWhere('id', 'like', '%' . $query . '%')
                           ->orderBy('created_at', 'desc');
        } else {
            $videos = Video::orderBy('created_at', 'desc');
        }    
    
        return view('admin.video-index', [
            'videos' => $videos->paginate(10)->onEachSide(1),
        ]);
    }    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->edit(request(), $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $video = Video::findOrFail($id);
    
        if ($request->filled('query')) {
            $query = $request->input('query');
            $reports = $video->abuseReports()
                ->where(function ($q) use ($query) {
                    $q->where('reason', 'LIKE', '%' . $query . '%')
                      ->orWhere('details', 'LIKE', '%' . $query . '%');
                })
                ->paginate(10);
        } else {
            $reports = $video->abuseReports()->paginate(10);
        }
    
        $domain = optional($video->user->userSetting)->player_domain;
        $domain = $domain ?: config('system.playerDefaultDomain');
    
        return view('admin.edit-video', compact('video', 'domain', 'reports'));
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Video::findOrFail($id);
        $data = $request->except(['_token', '_method']);
        $user->update($data);
        return redirect()->back()->with('success', "Video updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Video::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Video has been deleted successfully.');
    }
}
