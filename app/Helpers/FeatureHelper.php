<?php

namespace App\Helpers;
    
function featureEnabled($feature, $userPlan)
{
    return in_array($userPlan, config("features.$feature", []));
}
