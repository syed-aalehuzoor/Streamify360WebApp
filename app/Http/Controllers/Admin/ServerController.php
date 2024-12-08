<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ServerController extends Controller
{   

    public function index()
    {        
        $servers = Server::paginate(10);


        return view('admin.servers-table', [
            'servers' => $servers,
        ]);
    }

    public function create()
    {
        return view('admin.add-servers');
    }

    public function store(Request $request)
    {
        // Validate the incoming request data with conditional rules
        $request->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|ip|unique:servers,ip',
            'ssh_port' => 'required|integer',
            'username' => 'required|string|max:255',
            'type' => 'required|in:encoder,storage',
            'encoder_type' => 'required_if:type,encoder|nullable|string|max:255',
            'domain' => 'required_if:type,storage|nullable|regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)+[a-zA-Z]{2,}$/',
            'limit' => 'required_if:type,encoder|nullable|integer',
            'dedicated' => 'required_if:type,encoder|boolean',
            'userid' => 'required_if:dedicated,true|nullable|string|max:255',
        ]);

        // Check if a server with the same IP already exists
        $existingServer = Server::where('ip', $request->ip)->first();
    
        if ($existingServer) {
            return redirect()->route('admin-servers')->withErrors(['ip' => 'A server with this IP address already exists.']);
        }
    
        // Determine the public_userid based on whether the server is dedicated
        $publicUserId = $request->dedicated ? $request->userid : 'public';
    
        // Prepare data for server creation
        $serverData = [
            'name' => $request->name,
            'ip' => $request->ip,
            'ssh_port' => $request->ssh_port,
            'username' => $request->username,
            'domain' => $request->domain,
            'status' => 'pending', // Initial status
            'type' => $request->type,
            'public_userid' => $publicUserId, // Set based on the dedicated field
            'encoder_type' => $request->encoder_type,
            'total_videos' => 0,
            'limit' => $request->type === 'encoder' ? $request->limit : 0,
        ];
    
        // Create the server
        $server = Server::create($serverData);    
    
        // Prepare and run the process to execute the Python script
        $startkey = env('START_KEY');
        $pythonkey = env('PYTHON_KEY');
        $scriptPath = '../scripts/setup_server.py';
        $serverId = $server->id;
        $command = "python {$scriptPath} --id {$serverId}";
    
        $process = new Process([$startkey, $pythonkey, $scriptPath, '--id', $serverId],
            null,
            ['SYSTEMROOT' => getenv('SYSTEMROOT'), 'PATH' => getenv("PATH")]);
    
        $process->run();
    
        return redirect()->route('admin-servers')->with('success', 'Server added successfully.');
    }    
}
