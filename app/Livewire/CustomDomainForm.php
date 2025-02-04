<?php

namespace App\Livewire;

use App\Services\CloudflareService;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Jobs\DomainActivationCheck;

class CustomDomainForm extends Component
{
    public $domain;
    public $verifedDomain = 'streamify360.com';
    public $verified;

    protected $cloudflare;

    public function __construct()
    {
        $this->cloudflare = new CloudflareService();
    }

    public function mount()
    {
        $this->refreshDomainStatus();
    }

    public function refreshDomainStatus()
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->firstOrNew();
        
        if ($settings->player_domain && !$settings->player_domain_varified) {
            $zone = $this->cloudflare->getZoneByDomainName($settings->player_domain);
            if ($zone['status'] === 'active') {
                $settings->player_domain_varified = true;
                $settings->save();
            }
        }

        $this->domain = $settings->player_domain;
        $this->verified = $settings->player_domain_varified;
        if ($this->verified) $this->verifedDomain = $settings->player_domain;
    }

    public function saveDomain()
    {
        $this->resetErrorBag();
        
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->firstOrNew();

        if ($settings->player_domain !== $this->domain) {
            if (UserSetting::where('player_domain', $this->domain)->exists()) {
                $this->addError('domain', 'This domain is already in use.');
                return;
            }

            $this->cloudflare->deleteZoneByDomainName($settings->player_domain);
            $result = $this->cloudflare->createZone($this->domain);

            if ($result['success']) {
                $settings->player_domain = $this->domain;
                $settings->player_domain_varified = false;
                $settings->save();
                DomainActivationCheck::dispatch($this->domain);
                $this->verified = false;
            } else {
                $this->addError('domain', $result['message']);
            }
        }
    }

    public function checkDomainVerification()
    {
        $this->refreshDomainStatus();
    }

    public function render()
    {
        return view('livewire.custom-domain-form');
    }
}