<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;

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
                         ->paginate(10);
        } else {
            $videos = Video::paginate(10);
        }    
        return view('admin.video-index', [
            'videos' => $videos,
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
        return $this->edit($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $video = Video::findOrFail($id);
        return view('admin.edit-video', compact('video'));
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
