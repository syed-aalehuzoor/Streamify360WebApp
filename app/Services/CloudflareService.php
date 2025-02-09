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
        $response = $this->cloudflareRequest()->get("https://api.cloudflare.com/client/v4/zones/$ZONE_ID/activation_check");
        return $response->json();
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
            \Log::error("Failed to delete zone: {$domainName}");
        }
    }

    public function deleteAllDnsRecords($domainName)
    {
        $zone = $this->getZoneByDomainName($domainName);
        if (!$zone) {
            return ['success' => false, 'message' => "Zone not found for {$domainName}"];
        }

        // Step 1: Get all DNS records
        $response = $this->cloudflareRequest()->get("https://api.cloudflare.com/client/v4/zones/{$zone['id']}/dns_records");
        $result = $response->json();

        if (empty($result['result'])) {
            return ['success' => true, 'message' => "No DNS records found for {$domainName}"];
        }

        // Step 2: Collect all record IDs
        $recordIds = array_column($result['result'], 'id');

        // Step 3: Delete all records using batch API
        $deleteResponse = $this->cloudflareRequest()->post("https://api.cloudflare.com/client/v4/zones/{$zone['id']}/dns_records/batch", [
            "deletes" => array_map(fn($id) => ["id" => $id], $recordIds)
        ]);

        $deleteResult = $deleteResponse->json();

        return $deleteResponse->failed()
            ? ['success' => false, 'message' => $deleteResult['errors'][0]['message'] ?? 'Failed to delete records']
            : ['success' => true, 'message' => "Deleted all DNS records for {$domainName}"];
    }

    /**
     * Create an A record for a given domain pointing to the server's IP.
     */
    public function createARecord($domainName, $serverIp, $proxied = true, $ttl = 1)
    {
        $zone = $this->getZoneByDomainName($domainName);
        if (!$zone) {
            return ['success' => false, 'message' => "Zone not found for {$domainName}"];
        }

        $response = $this->cloudflareRequest()->post("https://api.cloudflare.com/client/v4/zones/{$zone['id']}/dns_records", [
            "type" => "A",
            "name" => $domainName,
            "content" => $serverIp,
            "ttl" => $ttl,
            "proxied" => $proxied,
        ]);

        $result = $response->json();

        return $response->failed()
            ? ['success' => false, 'message' => $result['errors'][0]['message'] ?? 'Failed to create record']
            : ['success' => true, 'message' => "A record created successfully", 'record' => $result['result']];
    }
}
