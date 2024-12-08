<?php

namespace App\Http\Controllers;

use Livewire\WithPagination;
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Server;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

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
        return view('videos', [
            'videos' => $videos,
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
        return view('drafts', [
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
        return view('add-video');
    }

    public function read($id) {
        $video = $this->getUserVideo($id);        
        return !$video ? $this->unauthorizedRedirect() : view('edit-video', compact('video'));
    }

    public function update($id) {
        $video = $this->getUserVideo($id);
    }

    public function delete($id) {
        $video = $this->getUserVideo($id);
        if (!$video) {
            return $this->unauthorizedRedirect();
        }
        $video->update(['status' => 'Deleted']);
        return redirect()->route('all-videos')->with('success', 'Video deleted successfully.');
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
            ->route('all-videos')
            ->with('error', 'Video not found or unauthorized action.');
    }
}
