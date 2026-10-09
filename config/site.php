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
        'products/neverseo' => 'product-neverseo',
        'products/crazify' => 'product-crazify',
        'about' => 'about',
        'contact' => 'contact',
    ],
];
