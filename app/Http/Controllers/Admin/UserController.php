<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Video;
use App\Models\AbuseReport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function tempLogin($id)
    {
        $user = User::findOrFail($id);
        $token = Str::random(40);
        Cache::put("temp_login_{$token}", $user->id, now()->addMinutes(10));
        return view('admin.temp-login', compact('token'));
    }

    public function index(Request $request)
    {
        $search   = $request->input('query');
        $verified = $request->input('verified');
        $usertype = $request->input('usertype');
        $userplan = $request->input('userplan');
        $action   = $request->input('action');
    
        // Build the query with filters
        $query = User::withCount(['videos', 'abuseReports'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($verified !== null && $verified !== '', function ($query) use ($verified) {
                if ($verified == '1') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            })
            ->when($usertype, function ($query, $usertype) {
                $query->where('usertype', $usertype);
            })
            ->when($userplan, function ($query, $userplan) {
                $query->where('userplan', $userplan);
            });
    
        // If the download CSV button was clicked, stream the CSV response
        if ($action === 'download_csv') {
            $users = $query->get();
            $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
    
            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');
                // CSV header row
                fputcsv($file, ['Name', 'Email', 'Verified', 'User Type', 'User Plan']);
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->name,
                        $user->email,
                        $user->email_verified_at ? 'Verified' : 'Not Verified',
                        $user->usertype,
                        $user->userplan
                    ]);
                }
                fclose($file);
            };
    
            return response()->stream($callback, 200, $headers);
        }
    
        // For normal search requests, continue with paginated view
        $planusers = [];
        $planusers['basic']      = User::where('userplan', 'basic')->count();
        $planusers['premium']    = User::where('userplan', 'premium')->count();
        $planusers['enterprise'] = User::where('userplan', 'enterprise')->count();
    
        // Fetch usertypes for the dropdown (if needed in your view)
        $usertypes = User::select('usertype', DB::raw('count(*) as count'))
                         ->groupBy('usertype')
                         ->get();
    
        return view('admin.user-index', [
            'users'         => $query->paginate(10)->onEachSide(1),
            'totalUsers'    => User::count(),
            'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
            'planusers'     => $planusers,
            'usertypes'     => $usertypes,
        ]);
    }
    


    public function edit($id)
    {
        $user = User::withCount(['videos', 'abuseReports'])->findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    public function show($id)
    {
        return $this->edit($id);
    }    

    public function create()
    {
        abort(404);    
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->except(['_token', '_method']);
        $user->update($data);
        return redirect()->back()->with('success', "User updated successfully.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'User has been deleted successfully.');
    }
}
