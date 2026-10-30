<?php

return [
    // URL prefixes are independent of Laravel locale codes (vi uses /vn).
    'locales' => [
        'en' => ['prefix' => '', 'label' => 'English', 'og' => 'en_US'],
        'vi' => ['prefix' => 'vn', 'label' => 'Tiếng Việt', 'og' => 'vi_VN'],
    ],
    // Public pages: route registration, sitemap and metadata share this catalog.
    'pages' => [
        '' => 'index',
        'products' => 'products',
        'products/saola' => 'product-saola',
        'products/gehihi' => 'product-gehihi',
        'products/neverseo' => 'product-neverseo',
        'products/crazify' => 'product-crazify',
        'products/buzz-messenger' => 'product-buzz-messenger',
        'products/activity-monitor-widget' => 'product-activity-monitor-widget',
        'about' => 'about',
        'contact' => 'contact',
    ],
];
