<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Livewire\WithPagination;
use App\Models\Video;
use App\Models\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ViewsOverTime;

class AnalyticsController extends Controller
{
    private const ACTIVE_STATUSES = ['Initiated', 'Processing', 'Live', 'Failed'];
    private const DRAFT_STATUS = ['Draft'];
    private const ITEMS_PER_PAGE = 10;

    public function updateViewsOverTime()
    {
        $today = now()->toDateString();
    
        // Fetch grouped views for today
        $VideoCurrentdayViews = View::whereDate('created_at', $today)
            ->selectRaw('videoid, DATE(created_at) as date, COUNT(*) as total_views')
            ->groupBy('videoid', 'date')
            ->get();
    
        foreach ($VideoCurrentdayViews as $todayViews) {
            // Fetch video creation date
            $videoCreationDate = Video::find($todayViews->videoid)->created_at;

            // Find all missing dates and update them
            collect(\Carbon\CarbonPeriod::create($videoCreationDate, $today))->diff(
                ViewsOverTime::where('videoid', $todayViews->videoid)
                    ->where('date', '<', $todayViews->date)
                    ->where('date', '>=', $videoCreationDate)
                    ->pluck('date')
            )->each(function ($date) use ($todayViews) {
                ViewsOverTime::updateOrCreate(
                    ['videoid' => $todayViews->videoid, 'date' => $date->toDateString()],
                    ['views' => View::where('videoid', $todayViews->videoid)
                        ->whereDate('created_at', $date->toDateString())
                        ->count() ?? 0]
                );
            });

            // Update today's views
            ViewsOverTime::updateOrCreate(
                ['videoid' => $todayViews->videoid, 'date' => $todayViews->date],
                ['views' => $todayViews->total_views]
            );
        }
    }
    
    public function video($id, Request $request)
    {
        // Parse the date range from the request
        $dateRange = $request->input('date_range', '');
        $dates = explode(' to ', $dateRange);
        $currentDate = now();
    
        // Retrieve the earliest available date for the video
        $firstAvailableDate = ViewsOverTime::where('videoid', $id)
            ->orderBy('date', 'asc')
            ->value('date') ?? $currentDate->copy()->subDays(28);
    
        // Determine the start and end dates, ensuring they fall within acceptable boundaries
        $startDate = isset($dates[0]) && !empty($dates[0]) 
            ? max(\Carbon\Carbon::parse($dates[0]), \Carbon\Carbon::parse($firstAvailableDate)) 
            : \Carbon\Carbon::parse($firstAvailableDate);
    
        $endDate = isset($dates[1]) && !empty($dates[1]) 
            ? min(\Carbon\Carbon::parse($dates[1]), $currentDate) 
            : $currentDate;
            
        // Fetch the views trend data
        $views_trend = ViewsOverTime::where('videoid', $id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->get();
        $total_views = $views_trend->sum('views');
        
        // Pass analytics data to the view
        return view('video-s-performance', [
            'views_trend' => $views_trend,
            'first_date' => $firstAvailableDate,
            'total_views' => $total_views,
        ]);
    }
    
    function getTopTenViews($column, $videoId) {
        return View::select($column, DB::raw('count(*) as views'))
            ->where('videoid', $videoId)
            ->groupBy($column)
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }

    public function videoAudiance($id)
    {
        $topfivecountries = $this->getTopTenViews('country', $id);
        $topfiveCities =  $this->getTopTenViews('city', $id);
        $topfiveDevices =  $this->getTopTenViews('device_type', $id);
        $topfiveRegions =  $this->getTopTenViews('region', $id);
        $totalViews = View::where('videoid', $id)->count();

        $calculatePercentage = function ($data, $keyName) use ($totalViews) {
            $formattedData = $data->map(function ($item) use ($totalViews, $keyName) {
                return [
                    'name' => $item->$keyName,
                    'views' => $item->views,
                    'percentage' => round(($item->views / $totalViews) * 100, 2),
                ];
            });
        
            $remainingPercentage = 100 - $formattedData->sum('percentage');
            if ($remainingPercentage > 0) {
                $formattedData[] = [
                    'name' => 'Others',
                    'percentage' => round($remainingPercentage, 2),
                ];
            }
        
            return $formattedData;
        };
        
        $countries = $calculatePercentage($topfivecountries, 'country');
        $cities = $calculatePercentage($topfiveCities, 'city');
        $regions = $calculatePercentage($topfiveRegions, 'region');
        $devices = $calculatePercentage($topfiveDevices, 'device_type');

        return view('audiance-analytics', [
            'countries' => $countries,
            'regions' => $regions,
            'cities' => $cities,
            'devices' => $devices,
        ]);
        
    }

    public function videos(Request $request)
    {
        $videos = $this->getFilteredVideos($request, self::ACTIVE_STATUSES);
        return view('video-performance', [
            'videos' => $videos,
            'query' => $request->input('query', ''),
        ]);
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
}
