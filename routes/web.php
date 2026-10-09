<?php

use Illuminate\Support\Facades\Route;
use Saola\Core\Support\SPA;
use Saola\Core\System;

SPA::active();
SPA::resetRoutes();
System::context('web')?->pushLaravelRoute();
SPA::inactive();

Route::get('/sitemap.xml', function () {
    $origin = rtrim(config('app.url'), '/');
    return response()->view('seo.sitemap', compact('origin'))
        ->header('Content-Type', 'application/xml; charset=UTF-8');
});

Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n")
        ->header('Content-Type', 'text/plain; charset=UTF-8');
});
