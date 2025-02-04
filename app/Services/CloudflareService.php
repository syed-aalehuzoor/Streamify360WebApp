<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudflareService
{
    private function cloudflareRequest()
    {
        return Http::withHeaders([
            "Content-Type" => "application/json",
            "X-Auth-Email" => config('services.cloudflare.email'),
            "X-Auth-Key" => config('services.cloudflare.api_key'),
            "Accept" => "application/json"
        ]);
    }

    public function runActivationCheck($ZONE_ID)
    {
        $response = Http::withHeaders([
            'X-Auth-Email' => config('services.cloudflare.email'),
            'X-Auth-Key' => config('services.cloudflare.api_key'),
            'Content-Type' => 'application/json',
        ])->get("https://api.cloudflare.com/client/v4/zones/$ZONE_ID/activation_check");
        $result = $response->json();
        return;
    }

    public function getZoneByDomainName($domainName)
    {
        $response = $this->cloudflareRequest()->get("https://api.cloudflare.com/client/v4/zones", [
            'name' => $domainName,
        ]);
        $result = $response->json();
        return !empty($result['result']) ? $result['result'][0] : false;
    }

    public function createZone($domainName)
    {
        $response = $this->cloudflareRequest()->post("https://api.cloudflare.com/client/v4/zones", [
            "account" => ["id" => config('services.cloudflare.account_id')],
            "name" => $domainName,
            "type" => "full"
        ]);
        return $response->failed()
            ? ['success' => false, 'message' => $response->json()['errors'][0]['message'] ?? 'An error occurred']
            : ['success' => $response->json()['success'], 'zone' => $response->json()['result'] ?? null];
    }

    public function deleteZoneByDomainName($domainName)
    {
        $zone = $this->getZoneByDomainName($domainName);
        if (!$zone) return;

        $response = $this->cloudflareRequest()->delete("https://api.cloudflare.com/client/v4/zones/{$zone['id']}");
        if ($response->failed()) {
            // Log error or throw exception
            \Log::error("Failed to delete zone: {$domainName}");
        }
    }
}
