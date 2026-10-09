{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach (config('site.pages') as $slug => $view)
    @foreach (config('site.locales') as $locale => $settings)
    <url>
        <loc>{{ $origin }}{{ site_url($slug, $locale) }}</loc>
        @foreach (config('site.locales') as $code => $language)
        <xhtml:link rel="alternate" hreflang="{{ $code }}" href="{{ $origin }}{{ site_url($slug, $code) }}" />
        @endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $origin }}{{ site_url($slug, \App\Support\Site::resolve('/')[0]) }}" />
    </url>
    @endforeach
@endforeach
</urlset>
