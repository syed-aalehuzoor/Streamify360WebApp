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
            'all-videos' => [
                'route' => 'videos.index',
                'label' => 'All Videos',
            ],
            'processes' => [
                'route' => 'Processes',
                'label' => 'Processes',
            ],
            'abuse-reports' => [
                'route' => 'abuse-reports',
                'label' => 'Abuse Reports',
            ],
        ],
    ],
    'servers' => [
        'label' => 'Servers',
        'icon' => 'fa-solid fa-server',
        'submenus' => [
            'all-servers' => [
                'route' => 'admin-servers',
                'label' => 'All Servers',
            ],
            'add-server' => [
                'route' => 'admin-add-server',
                'label' => 'Add Server',
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
    'settings' => [
        'label' => 'Settings',
        'icon' => 'fa-solid fa-wrench',
        'submenus' => [
            'video-settings' => [
                'route' => 'video-settings',
                'label' => 'Video Settings',
            ],
            'config-setting' => [
                'route' => 'config-setting',
                'label' => 'System Configuration',
            ],
        ],
    ],
];
