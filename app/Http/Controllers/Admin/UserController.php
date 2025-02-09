<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
    
        if ($query) {
            $users = User::where('name', 'like', '%' . $query . '%')
                         ->orWhere('email', 'like', '%' . $query . '%')
                         ->paginate(10);
        } else {
            $users = User::paginate(10);
        }    
        return view('admin.user-index', [
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
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
