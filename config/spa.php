<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SPA Scopes Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình các scope cho SPA system. Mỗi scope có thể có:
    | - name: Tên scope
    | - container: Container element ID
    | - mode: Router mode (history/hash)
    | - routes: Danh sách routes
    | - middleware: Middleware áp dụng
    |
    */

    'mode' => env('SPA_MODE', 'web'),
    'debug' => env('SPA_DEBUG', false),
    'name' => env('SPA_NAME', 'One App'),
    'base_url' => env('SPA_BASE_URL', env('APP_URL', 'http://localhost')),
    'router_mode' => env('SPA_ROUTER_MODE', 'history'),
];
