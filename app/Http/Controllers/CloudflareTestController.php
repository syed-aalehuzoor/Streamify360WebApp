<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CloudflareService;
use App\Jobs\DomainActivationCheck;

class CloudflareTestController extends Controller
{
    protected $cloudflareService;

    public function __construct(CloudflareService $cloudflareService)
    {
        $this->cloudflareService = $cloudflareService;
    }

    public function index()
    {
        return view('test');
    }

    public function handleRequest(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
            'action' => 'required|string',
        ]);

        $domain = $request->input('domain');
        $action = $request->input('action');
        $serverIp = $request->input('server_ip');

        switch ($action) {
            case 'get_zone':
                $response = $this->cloudflareService->getZoneByDomainName($domain);
                break;
            case 'create_zone':
                $response = $this->cloudflareService->createZone($domain);
                break;
            case 'delete_zone':
                $this->cloudflareService->deleteZoneByDomainName($domain);
                $response = ['success' => true, 'message' => "Zone deleted for {$domain}"];
                break;
            case 'delete_dns_records':
                $response = $this->cloudflareService->deleteAllDnsRecords($domain);
                break;
            case 'create_a_record':
                if (!$serverIp) {
                    return back()->with('message', 'Server IP is required for creating an A record.');
                }
                $response = $this->cloudflareService->createARecord($domain, $serverIp);
                break;
            case 'run_activation_check':
                $zone = $this->cloudflareService->getZoneByDomainName($domain);
                DomainActivationCheck::dispatch($domain);
                $response = ['success' => true, 'message' => "Activation check queued for {$domain}"];
                break;
            default:
                $response = ['success' => false, 'message' => 'Invalid action'];
        }

        return back()->with('message', json_encode($response, JSON_PRETTY_PRINT));
    }
}
