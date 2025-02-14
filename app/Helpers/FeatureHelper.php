<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('featureEnabled')) {
    function featureEnabled($feature, $userPlan)
    {
        return in_array($userPlan, config("features.$feature", []));
    }
}

if (!function_exists('userHasAccess')) {
    function userHasAccess(array $subItem): bool
    {
        $user = Auth::user();
        return $user && in_array($user->userplan, $subItem['plans']);
    }
}

if (!function_exists('isActiveSubmenu')) {
    function isActiveSubmenu(array $subItem): bool
    {
        $active = request()->routeIs($subItem['route']);
        if (isset($subItem['Division'])) {
            $active = $active && (request()->segment(2) == $subItem['Division']);
        }
        return $active;
    }
}
