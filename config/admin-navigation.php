<?php

return [
    'dashboard' => [
        'route' => 'admin',
        'label' => 'Dashboard',
        'icon' => 'fa-solid fa-tachometer-alt',
    ],
    'videos' => [
        'label' => 'Videos',
        'icon' => 'fa-solid fa-photo-film',
        'submenus' => [
            'admin-videos.index' => [
                'route' => 'admin-videos.index',
                'label' => 'All Videos',
            ],
            'abuse-reports' => [
                'route' => 'abuse-reports.index',
                'label' => 'Abuse Reports',
            ],
        ],
    ],
    'users' => [
        'label' => 'Users',
        'icon' => 'fa-solid fa-user',
        'submenus' => [
            'all-users' => [
                'route' => 'users.index',
                'label' => 'All Users',
            ],
        ],
    ],
    'servers' => [
        'label' => 'Servers',
        'icon' => 'fa-solid fa-server',
        'submenus' => [
            'all-servers' => [
                'route' => 'servers.index',
                'label' => 'All Servers',
            ],
        ],
    ],
    'settings' => [
        'label' => 'Settings',
        'icon' => 'fa-solid fa-gear',
        'submenus' => [
            'all-settings' => [
                'route' => 'system-settings.index',
                'label' => 'System Settings',
            ],
        ],
    ],
];
