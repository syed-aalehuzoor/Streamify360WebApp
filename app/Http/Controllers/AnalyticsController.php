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

            $audienceDimensions = ['country', 'region', 'city', 'device_type'];
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
        $dateRange = $request->input('date_range', '');
        $dates = explode(' to ', $dateRange);
        $currentDate = now();

        $firstAvailableDate = ViewsOverTime::where('videoid', $id)
            ->orderBy('date', 'asc')
            ->value('date') ?? $currentDate->subDays(28);

        $startDate = !empty($dates[0]) 
            ? max(\Carbon\Carbon::parse($dates[0]), \Carbon\Carbon::parse($firstAvailableDate)) 
            : \Carbon\Carbon::parse($firstAvailableDate);

        $endDate = !empty($dates[1]) 
            ? min(\Carbon\Carbon::parse($dates[1]), $currentDate) 
            : $currentDate;

        $viewsTrend = ViewsOverTime::where('videoid', $id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $viewsTrend = collect($startDate->daysUntil($endDate))->map(function ($date) use ($viewsTrend) {
            return $viewsTrend->get($date->toDateString(), ['date' => $date->toDateString(), 'views' => 0]);
        });

        $totalViews = $viewsTrend->sum('views');

        return view('analytics.performance-trend', [
            'views_trend' => $viewsTrend,
            'first_date' => $firstAvailableDate,
            'total_views' => $totalViews,
        ]);
    }

    private function getTopTenDimensionViews($column, $videoId)
    {
        return View::select($column, DB::raw('count(*) as views'))
            ->where('videoid', $videoId)
            ->groupBy($column)
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }

    public function showVideoAudienceInsights($id)
    {
        $audienceDimensions = ['country', 'region', 'city', 'device_type'];
        $insights = [];

        foreach ($audienceDimensions as $type) {
            $insights[$type] = AudienceInsight::where('videoid', $id)
                ->where('type', $type)
                ->orderByDesc('percentage')
                ->get();
        }

        return view('analytics.audience', [
            'countries' => $insights['country'],
            'regions' => $insights['region'],
            'cities' => $insights['city'],
            'devices' => $insights['device_type'],
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
