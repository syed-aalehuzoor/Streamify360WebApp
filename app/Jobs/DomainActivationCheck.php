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
    protected string $domain;
    protected CloudflareService $cloudflareService;
    public $tries = 24;
    protected $tries_gap = 3600; 

    /**
     * Create a new job instance.
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->cloudflareService = new CloudflareService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            \Log::info("Checking for domain: '{$this->domain}'");

            $settings = UserSetting::where('player_domain', trim($this->domain))->first();
            if (!$settings) {
                \Log::info("Domain .{$this->domain}. not found");
                return;
            }
             else if($settings->player_domain_varified) {
                \Log::info("Domain '{$this->domain}' Already varified");
                return;
            }
    
            $zone = $this->cloudflareService->getZoneByDomainName($this->domain);
            if (!$zone || !isset($zone['status'])) {
                throw new Exception("Failed to retrieve zone information for domain: {$this->domain}");
            }
    
            if ($zone['status'] === 'pending') {
                \Log::info("Domain {$this->domain} is still pending. Retrying in 1 hour.");
                $this->release($this->tries_gap);
                return;
            } 
            
            if ($zone['status'] === 'active') {
                $this->cloudflareService->deleteAllDnsRecords($this->domain);
                $this->cloudflareService->createARecord($this->domain, config('system.playerServerIP'));
                $settings->update(['player_domain_varified' => true]);
            }
        } catch (Exception $e) {
            \Log::error("Domain activation error for {$this->domain}: " . $e->getMessage());
            $this->failed($e);
        }
    }

    public function failed(Exception $exception): void
    {
        \Log::error("Domain activation failed for {$this->domain}: " . $exception->getMessage());

        // Mail::to('admin@example.com')->send(new DomainActivationFailed($this->domain));
    
        $settings = UserSetting::where('player_domain', $this->domain)->first();
        if ($settings) {
            $settings->domain_varification_failed = true;
            $settings->save();
        }
    }
}
