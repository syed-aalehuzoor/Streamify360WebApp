<?php

return [
    'dashboard' => [
        'route' => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'fa-solid fa-tachometer-alt',
    ],
    'videos' => [
        'label' => 'Videos',
        'icon' => 'fa-solid fa-photo-film',
        'submenus' => [
            'videos.add-new' => [
                'route' => 'videos.add-new',
                'label' => 'Upload New',
                'plans' => ['basic', 'premium', 'enterprise'],
                'tooltip' => 'Add New Video!'
            ],
            'videos.index' => [
                'route' => 'videos.index',
                'label' => 'All Videos',
                'plans' => ['basic', 'premium', 'enterprise'],
                'tooltip' => 'All Published Videos!'
            ],
            'videos.drafts' => [
                'route' => 'videos.drafts',
                'label' => 'Draft Videos',
                'plans' => ['basic', 'premium', 'enterprise'],
                'tooltip' => 'Draft Videos!'
            ],
        ],
    ],
    'analytics' => [
        'label' => 'Analytics',
        'icon' => 'fa-solid fa-chart-simple',
        'submenus' => [
            'performance-videos-list' => [
                'route' => 'performance-videos-list',
                'label' => 'Video Performance',
                'plans' => ['premium', 'enterprise'],
                'tooltip' => 'Coming Soon!'
            ],
            'audience-videos-list' => [
                'route' => 'audience-videos-list',
                'label' => 'Audience Insights',
                'plans' => ['premium', 'enterprise'],
                'tooltip' => 'Coming Soon!'
            ],
        ],
    ],
    'settings' => [
        'label' => 'Settings',
        'icon' => 'fa-solid fa-gear',
        'submenus' => [
            'player-settings.edit' => [
                'route' => 'player-settings.edit',
                'label' => 'Player Customization',
                'plans' => ['basic', 'premium', 'enterprise'],
                'tooltip' => 'Available on all plans'
            ],
            'ad-settings' => [
                'route' => 'ad-settings',
                'label' => 'Ad Settings',
                'plans' => ['premium', 'enterprise'],
                'tooltip' => 'Only available for Premium and Enterprise plans'
            ],
        ],
    ],
    'subscription' => [
        'label' => 'Subscription',
        'icon' => 'fa-solid fa-receipt',
        'submenus' => [
            'subscription' => [
                'route' => 'subscription',
                'label' => 'Subscription',
                'plans' => ['basic', 'premium', 'enterprise'],
                'tooltip' => 'Manage your subscription'
            ],
        ],
    ],
    'tools' => [
        'label' => 'Tools',
        'icon' => 'fa-solid fa-wrench',
        'submenus' => [
            'subtitle-translator' => [
                'route' => 'subtitle-translator',
                'label' => 'Subtitle Translator',
                'plans' => [],
                'tooltip' => 'Coming Soon!'
            ],
            'tools' => [
                'route' => 'tools',
                'label' => 'SRT to ASS',
                'plans' => [],
                'tooltip' => 'Coming Soon!'
            ],
        ],
    ],
];
