<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\PrivateKeyLoader;

class Servers extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servers = Server::paginate(10);
        return view('admin.server-index', [
            'servers' => $servers,
        ]);
    }    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->edit(request(), $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $server = Server::findOrFail($id);
        $ssh = new SSH2($server->ip);
        $privateKey = PublicKeyLoader::load(file_get_contents(base_path('scripts/streamify360.pem')));
        if (!$ssh->login($server->username, $privateKey)) return 'SSH Login Failed';
        $output = $ssh->exec("df -h --output=used,size / | tail -n 1 | awk '{print \$1 \"/\" \$2}'");
        $server->storage_stat = $output;
        return view('admin.server-edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $server = Server::find($id);
        $validatedData = $request->validate([
            'name'      => 'required|string|max:255',
            'ip'        => 'required|ip',
            'ssh_port'  => 'required|integer',
            'username'  => 'required|string|max:255',
            'domain'    => 'required|string|max:255',
        ]);
    
        $server->update($validatedData);
    
        return redirect()->back()->with('success', 'Server updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
