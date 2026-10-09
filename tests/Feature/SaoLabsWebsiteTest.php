<?php

namespace Tests\Feature;

use App\Modules\Home\Http\Controllers\Web\HomeController;
use App\Support\Site;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SaoLabsWebsiteTest extends TestCase
{
    private function pages(): array
    {
        $pages = [];
        foreach (config('site.pages') as $slug => $view) {
            foreach (array_keys(config('site.locales')) as $locale) {
                $pages[Site::url($slug, $locale)] = [$locale, $slug, $view];
            }
        }

        return $pages;
    }

    public function test_all_public_pages_render_with_unique_metadata_and_reciprocal_language_links(): void
    {
        config(['app.url' => 'https://saolabs.org']);
        $titles = [];
        foreach ($this->pages() as $path => [$locale, $slug, $view]) {
            $response = $this->get('http://untrusted.example'.$path.'?campaign=test')->assertOk()
                ->assertSee('<html lang="'.$locale.'">', false)
                ->assertSee('data-server-rendered="true"', false)
                ->assertSee('<link rel="canonical" href="https://saolabs.org'.$path.'">', false)
                ->assertSee('hreflang="en" href="https://saolabs.org/'.$slug.'"', false)
                ->assertSee('hreflang="vi" href="https://saolabs.org/vn'.($slug ? '/'.$slug : '').'"', false)
                ->assertSee('hreflang="x-default" href="https://saolabs.org/'.$slug.'"', false);
            $document = new \DOMDocument;
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $this->assertCount(1, $xpath->query('//*[@id="app-root"]'));
            $this->assertCount(1, $xpath->query('//*[@id="app-root"]//h1'));
            $this->assertCount(1, $xpath->query('//main[@id="main"]'));
            $this->assertDoesNotMatchRegularExpression('/site\.[a-z_]+\.[a-z_]+/', $xpath->query('//main')->item(0)->textContent);
            $languageLink = $xpath->query('//div[contains(@class, "language-options")]/a[@lang="'.($locale === 'vi' ? 'en' : 'vi').'"]')->item(0);
            $opposite = $locale === 'vi' ? '/'.$slug : '/vn'.($slug ? '/'.$slug : '');
            $this->assertSame($opposite, $languageLink->getAttribute('href'));
            $title = $xpath->query('//title')->item(0)->textContent;
            $this->assertNotContains($title, $titles);
            $titles[] = $title;
            $this->assertNotEmpty($xpath->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
            $this->assertSame($path, parse_url($xpath->query('//meta[@property="og:url"]')->item(0)->getAttribute('content'), PHP_URL_PATH));
            preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches);
            $organization = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('Organization', $organization['@type']);
            $this->assertSame('https://saolabs.org/', $organization['url']);
            $this->assertCount(4, $organization['member']);
            foreach ($xpath->query('//a[starts-with(@href,"/")]') as $link) {
                $destination = parse_url($link->getAttribute('href'), PHP_URL_PATH);
                $this->assertArrayHasKey($destination, $this->pages(), 'Broken internal link on '.$path);
            }
        }
        $this->assertCount(count($this->pages()), $titles);
    }

    public function test_content_has_moved_to_focused_pages_and_products_link_internally(): void
    {
        foreach (['' => 'en', '/vn' => 'vi'] as $prefix => $locale) {
            $home = $this->get($prefix ?: '/')->assertOk();
            foreach (['saola', 'neverseo', 'crazify'] as $product) {
                $home->assertSee('href="'.$prefix.'/products/'.$product.'"', false);
                $this->get($prefix.'/products/'.$product)->assertOk();
            }
            $home->assertSee('id="capabilities"', false)->assertSee('id="approach"', false)->assertSee('id="faq"', false);
            $home->assertSee(__('site.home.capabilities.architecture.description', [], $locale));
            $home->assertSee(__('site.home.faq.products.answer', [], $locale));
            $about = $this->get($prefix.'/about')->assertOk();
            foreach (['Lê Ngọc Doãn', 'Phạm Quang Linh', 'Phùng Đức Thắng', 'Vũ Minh Hiếu'] as $name) {
                $about->assertSee($name);
            }
            $this->get($prefix.'/contact')->assertOk()
                ->assertSee('href="mailto:support.saolabs@gmail.com"', false)
                ->assertSee('href="tel:+84382434682"', false);
        }
        $this->get('/vn/contact')->assertSee('tỉnh Phú Thọ, Việt Nam');
        $this->get('/products/missing')->assertNotFound();
        $this->get('/vn/products/missing')->assertNotFound();
    }

    public function test_saola_boot_registers_every_page_in_both_languages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        preg_match('/window\.APP_CONFIGS=(.*?)<\/script>/s', $html, $match);
        $boot = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('#app-root', $boot['container']);
        $this->assertSame('en', $boot['i18n']['locale']);
        $this->assertArrayHasKey('en', $boot['i18n']['messages']);
        $this->assertArrayNotHasKey('vi', $boot['i18n']['messages']);
        $routes = array_column($boot['router']['routes'], 'component', 'path');
        foreach ($this->pages() as $path => [$locale, $slug, $view]) {
            $this->assertSame('web.modules.home.'.$view, $routes[$path]);
        }
        preg_match('/window\.APP_CONFIGS=(.*?)<\/script>/s', $this->get('/vn')->getContent(), $match);
        $translatedBoot = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('vi', $translatedBoot['i18n']['locale']);
        $this->assertSame(__('site.home.hero.highlight', [], 'vi'), $translatedBoot['i18n']['messages']['vi']['groups']['site']['home']['hero']['highlight']);
    }

    public function test_shared_view_uses_laravel_translations_overrides_and_fallback(): void
    {
        trans('site', [], 'vi');
        app('translator')->addLines(['site.home.hero.highlight' => 'Bản dịch thay đổi khi chạy'], 'vi');
        $this->get('/vn')->assertOk()->assertSee('Bản dịch thay đổi khi chạy')
            ->assertDontSee('site.home.hero.highlight');
        $this->assertSame(__('site.home.hero.title', [], 'en'), __('site.home.hero.title', [], 'fr'));
    }

    public function test_an_additional_locale_uses_the_same_page_and_metadata_without_new_views(): void
    {
        config(['site.locales.fr' => ['prefix' => 'fr', 'label' => 'Français', 'og' => 'fr_FR']]);
        app('translator')->addLines(['site.home.hero.highlight' => 'IA en pratique.'], 'fr');
        // Simulate a request using the same controller and view as registered by the catalog.
        Route::get('/fr', [HomeController::class, 'show']);
        $this->get('/fr')->assertOk()->assertSee('<html lang="fr">', false)
            ->assertSee('IA en pratique.')->assertSee('hreflang="fr"', false)
            ->assertSee('href="/fr/products"', false)
            ->assertDontSee('site.home.hero.title');
        $this->assertSame('/fr/products/saola', Site::url('products/saola', 'fr'));
        $this->assertSame(['fr', 'products/saola'], Site::resolve('/fr/products/saola'));
        $sitemap = simplexml_load_string($this->get('/sitemap.xml')->assertOk()->getContent());
        $this->assertCount(count($this->pages()), $sitemap->url);
    }

    public function test_sitemap_lists_all_pages_with_the_correct_language_pairs(): void
    {
        config(['app.url' => 'https://saolabs.org']);
        $response = $this->get('/sitemap.xml')->assertOk();
        $document = simplexml_load_string($response->getContent());
        $this->assertNotFalse($document);
        $this->assertCount(count($this->pages()), $document->url);
        $seen = [];
        foreach ($document->url as $entry) {
            $path = parse_url((string) $entry->loc, PHP_URL_PATH);
            $this->assertArrayHasKey($path, $this->pages());
            $this->assertNotContains($path, $seen);
            $seen[] = $path;
            [$locale, $slug] = $this->pages()[$path];
            $alternates = [];
            foreach ($entry->children('http://www.w3.org/1999/xhtml')->link as $alternate) {
                $attributes = $alternate->attributes();
                $alternates[(string) $attributes['hreflang']] = (string) $attributes['href'];
            }
            $this->assertSame('https://saolabs.org/'.$slug, $alternates['en']);
            $this->assertSame('https://saolabs.org/vn'.($slug ? '/'.$slug : ''), $alternates['vi']);
        }
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://saolabs.org/sitemap.xml');
        $this->get('/up')->assertOk();
        $this->get('/missing-page')->assertNotFound();
    }
}
