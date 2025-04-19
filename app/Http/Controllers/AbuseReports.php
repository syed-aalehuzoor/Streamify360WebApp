<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbuseReport;

class AbuseReports extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = AbuseReport::selectRaw('video_id, COUNT(*) as report_count')
            ->groupBy('video_id')
            ->paginate(10);

        return view('admin.abuse-reports', ['reports' => $reports]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        return view('presentation.report-content', ['id' => $id]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'reason' => 'required|string',
            'details' => 'nullable|string',
        ]);
        AbuseReport::create($request->all());
        return back()->with('success', 'Your report has been submitted.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $videoId, ?string $reportId = null)
    {
        if ($reportId) {
            // Delete a single report ensuring it belongs to the specified video.
            $report = AbuseReport::where('id', $reportId)
                                 ->where('video_id', $videoId)
                                 ->firstOrFail();
            $report->delete();
            $message = "Report deleted successfully.";
        } else {
            // Delete all reports for the given video.
            AbuseReport::where('video_id', $videoId)->delete();
            $message = "All reports deleted successfully.";
        }
    
        return redirect()->back()->with('success', $message);
    }
    
}
