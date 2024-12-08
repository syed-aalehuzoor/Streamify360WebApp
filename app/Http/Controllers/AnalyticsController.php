<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function video()
    {
        return view('video-analytics');
    }

    public function audiance()
    {
        return view('audiance-analytics');
    }
}
