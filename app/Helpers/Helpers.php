<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('userHasAccess')) {
    /**
     * Determine if the current user has access based on their plan.
     *
     * @param  array  $subItem
     * @return bool
     */
    function userHasAccess(array $subItem): bool
    {
        return in_array(Auth::user()->userplan, $subItem['plans']);
    }
}

if (! function_exists('isActiveSubmenu')) {
    /**
     * Determine if a submenu item should be marked as active.
     *
     * @param  array  $subItem
     * @return bool
     */
    function isActiveSubmenu(array $subItem): bool
    {
        $active = request()->routeIs($subItem['route']);
        if (isset($subItem['Division'])) {
            $active = $active && (request()->segment(2) == $subItem['Division']);
        }
        return $active;
    }
}
