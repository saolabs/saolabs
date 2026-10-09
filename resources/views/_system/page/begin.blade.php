@php
    $origin = rtrim(config('app.url'), '/');
    $locale = app()->getLocale();
    $slug = trim($__env->yieldContent('seo:path'), '/');
    $canonical = $origin.site_url($slug);
    $defaultPath = site_url($slug, \App\Support\Site::resolve('/')[0]);
    $organization = [
        '@context' => 'https://schema.org', '@type' => 'Organization',
        'name' => 'SaoLabs', 'url' => $origin.'/',
        'description' => __('site.common.organization.description'),
        'email' => 'support.saolabs@gmail.com', 'telephone' => '+84 382 434 682',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Số 172, đường Bà Triệu, phường Thống Nhất',
            'addressRegion' => 'Phú Thọ', 'addressCountry' => 'VN'],
        'member' => array_map(fn ($name) => ['@type' => 'Person', 'name' => $name],
            ['Lê Ngọc Doãn', 'Phạm Quang Linh', 'Phùng Đức Thắng', 'Vũ Minh Hiếu']),
    ];
@endphp
<!DOCTYPE html>
<html lang="@yield('html:lang', 'en')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('meta:title', 'SaoLabs — Software for what comes next.')</title>
    <meta name="description" content="@yield('meta:description')">
    <meta name="author" content="SaoLabs">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach (config('site.locales') as $code => $settings)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $origin }}{{ site_url($slug, $code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $origin }}{{ $defaultPath }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SaoLabs">
    <meta property="og:title" content="@yield('meta:title')">
    <meta property="og:description" content="@yield('meta:description')">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ config('site.locales')[$locale]['og'] }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('meta:title')">
    <meta name="twitter:description" content="@yield('meta:description')">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preload" href="/assets/cartzilla/fonts/inter-variable-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/cartzilla/icons/cartzilla-icons.min.css">
    <link rel="stylesheet" href="/assets/cartzilla/css/theme.min.css">
    <link rel="stylesheet" href="/assets/css/site.css">
    <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @include($__system__.'partials.assets')
    {!! $__helper->renderHeadAssets('css') !!}
</head>
<body>
    <div id="app-root" data-server-rendered="true">
