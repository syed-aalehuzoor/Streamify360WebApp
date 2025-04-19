<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AzureBatchService;
use Illuminate\Http\JsonResponse;
use Exception;

use App\Models\Users;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use App\Services\AzureStorageService;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;

class HomeController extends Controller
{
    public function testanalytics(){
        return view('test');
    }

    public function index()
    {
        if(Auth::user()->usertype=='admin')
        {
            return redirect('/admin');
        }
        else if(Auth::user()->usertype=='user'){
            return redirect('/dashboard');
        }
        abort(404);
        
    }
    
    public function dashboard(){
        $user = Auth::user();
        $totalViews = Video::where('userid', $user->id)->sum('views');
        $totalVideos = Video::where('userid', $user->id)->count();
        return view('dashboard', compact('user', 'totalViews', 'totalVideos'));
    }
}
