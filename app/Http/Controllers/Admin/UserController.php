<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    public function index()
    {        
        $users = User::paginate(10);

        return view('admin.user-table', [
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        // Fetch the video from the database
        $user = User::findOrFail($id);
        
        // Return the view with the video data
        return view('admin.edit-user', compact('user'));
    }

    // Suspend the user account
    public function suspend($id)
    {
        $user = User::findOrFail($id);
        $user->user_status = 'suspended'; // Update status to suspended
        $user->save();

        return redirect()->back()->with('success', 'User has been suspended successfully.');
    }

    // Reactivate the user account
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->user_status = 'active'; // Update status to active
        $user->save();

        return redirect()->back()->with('success', 'User has been reactivated successfully.');
    }

    // Delete the user account
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // Delete the user

        return redirect()->back()->with('success', 'User has been deleted successfully.');
    }
}
