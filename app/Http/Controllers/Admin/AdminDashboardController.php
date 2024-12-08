<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function videos()
    {
        return view('admin.videos');
    }

    public function processes()
    {
        return view('admin.processes');
    }

    public function abuseReports()
    {
        return view('admin.abuse-reports');
    }
}
