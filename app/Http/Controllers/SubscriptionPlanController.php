<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Pass the user, total views, and total videos to the view
        return view('subscription-index', compact('user'));
    }
}
