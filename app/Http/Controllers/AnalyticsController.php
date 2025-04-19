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
use App\Models\AudienceInsight;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;

class AnalyticsController extends Controller
{
    private const ACTIVE_STATUSES = ['Initiated', 'Processing', 'Live', 'Failed'];
    private const DRAFT_STATUS = ['Draft'];
    private const ITEMS_PER_PAGE = 10;


    public function synchronizeViewsOverTime()
    {
        $dailyViewGroups = View::selectRaw('videoid, DATE(created_at) as date, COUNT(*) as total_views')
            ->groupBy('videoid', 'date')
            ->get();

        foreach ($dailyViewGroups as $dailyViewsRecord) {
            $record = ViewsOverTime::firstOrCreate(
                ['videoid' => $dailyViewsRecord->videoid, 'date' => $dailyViewsRecord->date],
                ['views' => 0]
            );
            $record->increment('views', $dailyViewsRecord->total_views);
        }
    }

    public function synchronizeAudienceInsights()
    {
        $videoViewGroups = View::selectRaw('videoid, COUNT(*) as videoTotalViews')
            ->groupBy('videoid')
            ->get();

        foreach ($videoViewGroups as $videoViewGroup) {
            $videoId = $videoViewGroup->videoid;
            $videoTotalViews = $videoViewGroup->videoTotalViews;

            $calculateInsightsPercentage = function ($viewData, $dimensionKey) use ($videoTotalViews) {
                $insightData = $viewData->map(fn($item) => [
                    'name' => $item->$dimensionKey,
                    'views' => $item->views,
                    'percentage' => round(($item->views / $videoTotalViews) * 100, 2),
                ]);

                $remainingViews = $videoTotalViews - $insightData->sum('views');
                if ($remainingViews > 0) {
                    $insightData[] = [
                        'name' => 'Others',
                        'views' => $remainingViews,
                        'percentage' => round(100 - $insightData->sum('percentage'), 2),
                    ];
                }

                return $insightData;
            };

            $audienceDimensions = ['country', 'region', 'city', 'deviceCategory'];
            $audienceInsights = collect($audienceDimensions)->mapWithKeys(fn($dimension) => [
                $dimension => $calculateInsightsPercentage($this->getTopTenDimensionViews($dimension, $videoId), $dimension)
            ]);

            foreach ($audienceInsights as $dimensionType => $insightData) {
                foreach ($insightData as $insight) {
                    AudienceInsight::updateOrCreate(
                        ['videoid' => $videoId, 'type' => $dimensionType, 'name' => $insight['name']],
                        ['views' => $insight['views'], 'percentage' => $insight['percentage']]
                    );
                }
            }
        }
    }

    public function refreshAnalyticsData()
    {
        $views = View::all();
        $this->synchronizeViewsOverTime();
        $this->synchronizeAudienceInsights();
        View::whereIn('id', $views->pluck('id'))->delete();
    }

    public function showVideoPerformance($id, Request $request)
    {
        $user = Auth::user();
        $video = $user->videos()->where('id', $id)->first();
        if (!$video) abort(404);
        $pagePath = '/video/' . $id;
        $dateRange = $request->input('date_range', '');
    
        // Determine start and end dates based on input
        if ($dateRange && count($dates = explode(' to ', $dateRange)) === 2) {
            $startDate = Carbon::parse($dates[0]);
            $endDate = Carbon::parse($dates[1]);
        } else {
            $endDate = Carbon::today();
            $startDate = Carbon::today()->subDays(7);
        }
        
        $period = Period::create($startDate, $endDate);
    
        $metrics = ['screenPageViews'];
        $dimensions = ['date'];
    
        $dimensionFilter = new FilterExpression([
            'filter' => new Filter([
                'field_name' => 'pagePath',
                'string_filter' => new StringFilter([
                    'match_type' => MatchType::EXACT,
                    'value' => $pagePath,
                ]),
            ]),
        ]);
    
        $analyticsData = Analytics::get($period, $metrics, $dimensions, 10, [], 0, $dimensionFilter);
    
        // Build an associative array from the analytics data, keyed by date.
        $analyticsViews = [];
        if ($analyticsData && $analyticsData->count() > 0) {
            foreach ($analyticsData as $row) {
                $dateStr = $row['date']->toDateString();
                $analyticsViews[$dateStr] = (int) $row['screenPageViews'];
            }
        }
    
        $viewsTrend = [];
        $totalViews = 0;
    
        // Iterate over each day in the period
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->toDateString();
            // Use the view count from analytics if available, otherwise default to zero.
            $views = isset($analyticsViews[$dateStr]) ? $analyticsViews[$dateStr] : 0;
            $viewsTrend[] = ['date' => $dateStr, 'views' => $views];
            $totalViews += $views;
        }
    
        usort($viewsTrend, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
    
        return view('analytics.performance-trend', [
            'views_trend' => $viewsTrend,
            'total_views' => $totalViews,
        ]);
    }

    public function showVideoAudienceInsights($id, Request $request)
    {
        $user = Auth::user();
        $video = $user->videos()->where('id', $id)->first();
        if (!$video) {
            #abort(404);
        }
        
        // Define the page path for the video
        $pagePath = '/video/' . $id;
        
        // Set a default period (last 7 days)
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(7);
        $period = Period::create($startDate, $endDate);
    
        // The audience dimensions we want to analyze
        $audienceDimensions = ['country', 'region', 'city', 'deviceCategory'];
        $insights = [];
    
        // Build a filter expression to limit the data to the current video page
        $dimensionFilter = new FilterExpression([
            'filter' => new Filter([
                'field_name' => 'pagePath',
                'string_filter' => new StringFilter([
                    'match_type' => MatchType::EXACT,
                    'value' => $pagePath,
                ]),
            ]),
        ]);
    
        // Loop through each audience dimension to get analytics data
        foreach ($audienceDimensions as $dimension) {
            $analyticsData = Analytics::get($period, ['screenPageViews'], [$dimension], 10, [], 0, $dimensionFilter);
            $dataPoints = [];
            $totalViews = 0;

            if ($analyticsData && $analyticsData->count() > 0) {
                foreach ($analyticsData as $row) {
                    $value = $row[$dimension];
                    $views = (int) $row['screenPageViews'];
                    $totalViews += $views;
                    $dataPoints[] = ['name' => $value, 'views' => $views];
                }
            }
    
            // Calculate the percentage share for each item
            $dimensionResult = [];
            foreach ($dataPoints as $point) {
                $percentage = $totalViews > 0 ? round(($point['views'] / $totalViews) * 100, 2) : 0;
                $dimensionResult[] = [
                    'name' => $point['name'],
                    'percentage' => $percentage,
                ];
            }
    
            $insights[$dimension] = $dimensionResult;
        }
    
        // Return the view with the analytics insights for each audience dimension
        return view('analytics.audience', [
            'countries' => $insights['country'] ?? [],
            'regions'   => $insights['region'] ?? [],
            'cities'    => $insights['city'] ?? [],
            'devices'   => $insights['deviceCategory'] ?? [],
        ]);
    }
    

    public function listPerformanceVideos(Request $request)
    {
        $videos = $this->filterVideos($request, self::ACTIVE_STATUSES);
        return view('analytics.performance-index', [
            'videos' => $videos,
            'query' => $request->input('query', ''),
        ]);
    }

    public function listAudienceVideos(Request $request)
    {
        $videos = $this->filterVideos($request, self::ACTIVE_STATUSES);
        return view('analytics.audience-index', [
            'videos' => $videos,
            'query' => $request->input('query', ''),
        ]);
    }

    /**
     * Filter videos for the authenticated user
     * 
     * @param Request $request
     * @param array $statuses
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function filterVideos(Request $request, array $statuses)
    {
        return Video::where('userid', Auth::id())
            ->where('name', 'like', '%' . $request->input('query', '') . '%')
            ->whereIn('status', $statuses)
            ->orderBy('created_at', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);
    }
}
