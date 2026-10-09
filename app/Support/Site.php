<?php

namespace App\Support;

use InvalidArgumentException;

class Site
{
    public static function resolve(string $path): array
    {
        $path = trim($path, '/');
        foreach (config('site.locales') as $locale => $settings) {
            $prefix = $settings['prefix'];
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix.'/'))) {
                return [$locale, ltrim(substr($path, strlen($prefix)), '/')];
            }
        }

        $default = array_key_first(array_filter(config('site.locales'), fn ($language) => $language['prefix'] === ''));

        return [$default, $path];
    }

    public static function url(string $slug = '', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $settings = config('site.locales')[$locale] ?? throw new InvalidArgumentException('Unsupported locale: '.$locale);

        return '/'.trim($settings['prefix'].'/'.ltrim($slug, '/'), '/');
    }

    public static function languages(): array
    {
        [, $slug] = self::resolve(request()->path());
        $languages = [];
        foreach (config('site.locales') as $locale => $settings) {
            $languages[] = ['locale' => $locale, 'label' => $settings['label'],
                'url' => self::url($slug, $locale), 'current' => $locale === app()->getLocale()];
        }

        return $languages;
    }

    public static function translations(): array
    {
        $messages = [];
        foreach (array_unique([app()->getLocale(), config('app.fallback_locale')]) as $locale) {
            // Laravel owns loading, overrides and fallback; the browser receives
            // the same catalog for hydration, with no translation fetch needed.
            $messages[$locale] = ['groups' => ['site' => trans('site', [], $locale)]];
        }

        return ['locale' => app()->getLocale(), 'fallbackLocale' => config('app.fallback_locale'), 'messages' => $messages];
    }
}
