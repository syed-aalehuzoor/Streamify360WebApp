<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Video;
use App\Models\UserSetting;

class EnforcePlaybackDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->route('id');
        $video = Video::findOrFail($id);
        $user_domain = UserSetting::where('user_id', $video->userid)->value('player_domain') ?? config('system.playerDefaultDomain');
        $allowedDomains = [$user_domain, config('system.playerDefaultDomain')];
        if (in_array($request->getHost(), $allowedDomains)) {
            return $next($request);
        }
        abort(404);        
    }
}
