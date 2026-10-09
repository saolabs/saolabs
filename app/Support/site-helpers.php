<?php

use App\Support\Site;

function site_url(string $slug = '', ?string $locale = null): string
{
    return Site::url($slug, $locale);
}

function site_locale(): string
{
    return app()->getLocale();
}

function site_slug(): string
{
    return Site::resolve(request()->path())[1];
}

function site_languages(): array
{
    return Site::languages();
}
