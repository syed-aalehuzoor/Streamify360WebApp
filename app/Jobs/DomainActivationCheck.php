<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use App\Services\CloudflareService;
use App\Models\UserSetting;
use Exception;

class DomainActivationCheck implements ShouldQueue
{
    use Queueable;
    protected $domain;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }
    
    public function backoff(): array
    {
        return [60*10, 60*60, 60*60];
    }

    /**
     * Execute the job.
     */
    public function handle(CloudflareService $cloudflareService): void
    {
        $settings = UserSetting::where('player_domain', $this->domain)->first();
        if (!$settings || $settings->player_domain_varified) {
            return;
        }
        $zone = $cloudflareService->getZoneByDomainName($this->domain);
        if (!$zone || !isset($zone['status'])) {
            \Log::error("Failed to retrieve zone information for domain: {$this->domain}");
            return;
        }

        if ($zone['status'] === 'pending') {
            $cloudflareService->runActivationCheck($zone['id']);
            throw new Exception('Domain is still pending. Retrying activation check.');
        } 
        
        if ($zone['status'] === 'active') {
            $settings->player_domain_varified = true;
            $settings->save();
        }
    }
}
