<?php

namespace App\Http\Controllers;

use Livewire\WithPagination;
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Server;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Http;
use App\Jobs\DeleteVideo;
use App\Models\UserSetting;

class VideoController extends Controller
{
    /**
     * Valid video statuses
     */
    private const ACTIVE_STATUSES = ['Initiated', 'Processing', 'Live', 'Failed'];
    private const DRAFT_STATUS = ['Draft'];
    private const ITEMS_PER_PAGE = 10;

    /**
     * Display a listing of active videos
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request) {
        $videos = $this->getFilteredVideos($request, self::ACTIVE_STATUSES);
        $domain = Auth::user()->userSetting->player_domain;
        if (!$domain) $domain = config('system.playerDefaultDomain');
        return view('videos.index', [
            'videos' => $videos,
            'domain' => $domain,
            'query' => $request->input('query', ''),
        ]);
    }


    /**
     * Display a listing of draft videos
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function drafts(Request $request) {
        $videos = $this->getFilteredVideos($request, self::DRAFT_STATUS);
        return view('videos.drafts', [
            'videos' => $videos,
            'query' => $request->input('query', ''),
        ]);
    }

    /**
     * Show the form for creating a new video
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('videos.add-new');
    }

    public function read($id) {
        $video = $this->getUserVideo($id);        
        return !$video ? $this->unauthorizedRedirect() : view('videos.edit', compact('video'));
    }

    public function update($id) {
        $video = $this->getUserVideo($id);
    }

    public function delete($id) {
        $video = $this->getUserVideo($id);
        if (!$video) {
            return $this->unauthorizedRedirect();
        }
        $video->status = 'Deleted';
        $video->save();
        DeleteVideo::dispatch($id);
        return redirect()->route('videos.index')->with('success', 'Video Deleted Successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $selectedVideos = $request->input('selected_videos');
        
        foreach ($selectedVideos as $videoid){
            $video = $this->getUserVideo($videoid);
            if (!$video) {
                return $this->unauthorizedRedirect();
            }
            $video->status = 'Deleted';
            $video->save();
            DeleteVideo::dispatch($videoid);
        }
        return redirect()->route('videos.index')->with('success', 'Videos Deleted Successfully.');
    }

    /**
     * Get filtered videos for the authenticated user
     * 
     * @param Request $request
     * @param array $statuses
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function getFilteredVideos(Request $request, array $statuses) {
        return Video::where('userid', Auth::id())
            ->where('name', 'like', '%' . $request->input('query', '') . '%')
            ->whereIn('status', $statuses)
            ->orderBy('created_at', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);
    }

    /**
     * Get video for the authenticated user
     * 
     * @param int $id
     * @return Video|null
     */
    private function getUserVideo($id) {
        return Video::where('id', $id)
            ->where('userid', Auth::id())
            ->first();
    }

    /**
     * Get unauthorized redirect response
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    private function unauthorizedRedirect() {
        return redirect()
            ->route('videos.index')
            ->with('error', 'Video not found or unauthorized action.');
    }
}
