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
                ->assertSee('hreflang="en" href="https://saolabs.org/'.($slug ? $slug.'.html' : '').'"', false)
                ->assertSee('hreflang="vi" href="https://saolabs.org/vn'.($slug ? '/'.$slug.'.html' : '').'"', false)
                ->assertSee('hreflang="x-default" href="https://saolabs.org/'.($slug ? $slug.'.html' : '').'"', false);
            $document = new \DOMDocument;
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $this->assertCount(1, $xpath->query('//*[@id="app-root"]'));
            $this->assertCount(1, $xpath->query('//*[@id="app-root"]//h1'));
            $this->assertCount(1, $xpath->query('//main[@id="main"]'));
            $this->assertDoesNotMatchRegularExpression('/site\.[a-z_]+\.[a-z_]+/', $xpath->query('//main')->item(0)->textContent);
            $languageLink = $xpath->query('//div[contains(@class, "language-options")]/a[@lang="'.($locale === 'vi' ? 'en' : 'vi').'"]')->item(0);
            $opposite = $locale === 'vi' ? '/'.($slug ? $slug.'.html' : '') : '/vn'.($slug ? '/'.$slug.'.html' : '');
            $this->assertSame($opposite, $languageLink->getAttribute('href'));
            $title = $xpath->query('//title')->item(0)->textContent;
            $this->assertNotContains($title, $titles);
            $titles[] = $title;
            $this->assertNotEmpty($xpath->query('//meta[@name="description"]')->item(0)->getAttribute('content'));
            $this->assertSame($path, parse_url($xpath->query('//meta[@property="og:url"]')->item(0)->getAttribute('content'), PHP_URL_PATH));
            preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches);
            $organization = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame('Organization', $organization['@type']);
            $this->assertSame('support@saolabs.org', $organization['email']);
            foreach ($xpath->query('//a[starts-with(@href,"mailto:")]') as $emailLink) {
                $this->assertSame('mailto:support@saolabs.org', $emailLink->getAttribute('href'));
            }
            $this->assertSame('https://saolabs.org/', $organization['url']);
            $this->assertCount(4, $organization['member']);
            foreach ($xpath->query('//a[starts-with(@href,"/")]') as $link) {
                $destination = parse_url($link->getAttribute('href'), PHP_URL_PATH);
                if (str_starts_with($destination, '/assets/')) {
                    $this->assertFileExists(public_path(ltrim($destination, '/')), 'Missing linked image on '.$path);
                    continue;
                }
                $this->assertArrayHasKey($destination, $this->pages(), 'Broken internal link on '.$path);
                $this->assertNotSame('false', $link->getAttribute('data-nav'), 'Internal navigation disabled on '.$path);
            }
        }
        $this->assertCount(count($this->pages()), $titles);
    }

    public function test_content_has_moved_to_focused_pages_and_products_link_internally(): void
    {
        foreach (['' => 'en', '/vn' => 'vi'] as $prefix => $locale) {
            $home = $this->get($prefix ?: '/')->assertOk();
            foreach (['saola', 'gehihi', 'neverseo', 'crazify', 'buzz-messenger', 'activity-monitor-widget'] as $product) {
                $home->assertSee('href="'.$prefix.'/products/'.$product.'.html"', false);
                $this->get($prefix.'/products/'.$product.'.html')->assertOk();
            }
            $home->assertSee('id="capabilities"', false)->assertSee('id="approach"', false)->assertSee('id="faq"', false);
            $home->assertSee(__('site.home.capabilities.architecture.description', [], $locale));
            $home->assertSee(__('site.home.faq.products.answer', [], $locale));
            $about = $this->get($prefix.'/about.html')->assertOk();
            foreach (['Lê Ngọc Doãn', 'Phạm Quang Linh', 'Phùng Đức Thắng', 'Vũ Minh Hiếu'] as $name) {
                $about->assertSee($name);
            }
            $this->get($prefix.'/contact.html')->assertOk()
                ->assertSee('href="mailto:support@saolabs.org"', false)
                ->assertSee('href="tel:+84382434682"', false);
        }
        $this->get('/vn/contact.html')->assertSee('tỉnh Phú Thọ, Việt Nam');
        $this->get('/products/missing.html')->assertNotFound();
        $this->get('/vn/products/missing.html')->assertNotFound();
    }

    public function test_gehihi_is_presented_as_an_agent_platform_in_both_languages(): void
    {
        foreach (['' => 'en', '/vn' => 'vi'] as $prefix => $locale) {
            $this->get($prefix.'/products/gehihi.html')->assertOk()
                ->assertSee(trans('site.products.gehihi.pitch', [], $locale))
                ->assertSee(trans('site.products.gehihi.features.knowledge.title', [], $locale))
                ->assertSee('href="https://app.gehihi.vn/ai/agents"', false)
                ->assertSee('href="https://gehihi.vn/"', false)
                ->assertDontSee('site.products.gehihi.');
            $this->get($prefix.'/products.html')->assertOk()
                ->assertSee('href="'.$prefix.'/products/gehihi.html"', false)
                ->assertSee(trans('site.products.gehihi.pitch', [], $locale));
        }
    }

    public function test_desktop_products_prioritize_github_and_keep_windows_linux_downloads(): void
    {
        $buzzRepository = 'https://github.com/saolabs/buzz-messenger';
        $widgetRepository = 'https://github.com/saolabs/Activity-Monitor-Widget';
        $widgetVersions = $widgetRepository.'/tree/main/dist';

        foreach (['', '/vn'] as $prefix) {
            $this->get($prefix.'/products.html')->assertOk()
                ->assertSee('href="'.$buzzRepository.'"', false)
                ->assertSee('href="'.$widgetRepository.'"', false)
                ->assertSee('href="'.$prefix.'/products/buzz-messenger.html#downloads"', false)
                ->assertSee('href="'.$prefix.'/products/activity-monitor-widget.html#downloads"', false);

            $buzz = $this->get($prefix.'/products/buzz-messenger.html')->assertOk();
            $document = new \DOMDocument;
            $document->loadHTML($buzz->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $this->assertSame($buzzRepository, $xpath->query('//main//div[contains(@class,"desktop-actions")]/a[1]')->item(0)->getAttribute('href'));
            $this->assertCount(6, $xpath->query('//*[@id="downloads"]//div[contains(concat(" ", normalize-space(@class), " "), " download-links ")]/a[@data-nav="false"]'));
            $this->assertCount(2, $xpath->query('//*[@id="downloads"]//a[@href="'.$buzzRepository.'/releases/tag/v0.2.1"]'));
            $this->assertCount(0, $xpath->query('//main//a[contains(@href,"releases/download/") and (contains(@href,".dmg") or contains(@href,".zip"))]'));
            foreach (['win-x64-setup.exe', 'win-x64-portable.exe', 'linux-x86_64.AppImage', 'linux-amd64.deb'] as $installer) {
                $buzz->assertSee('href="'.$buzzRepository.'/releases/download/v0.2.1/buzz-messenger-0.2.1-'.$installer.'"', false);
            }
            $buzz->assertSee('href="'.$buzzRepository.'/releases/download/v0.2.1/SHA256SUMS.txt"', false);

            $widget = $this->get($prefix.'/products/activity-monitor-widget.html')->assertOk()
                ->assertSee('href="'.$widgetVersions.'"', false)
                ->assertSee('href="'.$widgetRepository.'/blob/main/dist/System-Activity-v0.2.0.dmg.sha256"', false)
                ->assertSee('Apple Silicon (ARM64)')
                ->assertSee('macOS 14');
            $document->loadHTML($widget->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            $this->assertSame($widgetRepository, $xpath->query('//main//div[contains(@class,"desktop-actions")]/a[1]')->item(0)->getAttribute('href'));
            $this->assertCount(0, $xpath->query('//main//a[contains(@href,"raw.githubusercontent.com")]'));
            $this->assertStringNotContainsString('Intel', $xpath->query('//*[@id="downloads"]')->item(0)->textContent);

            foreach ([$prefix ?: '/', $prefix.'/products.html'] as $path) {
                $document->loadHTML($this->get($path)->assertOk()->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
                $xpath = new \DOMXPath($document);
                $actions = $xpath->query('//main//div[contains(@class,"desktop-actions")]/a[1]');
                $this->assertCount(2, $actions);
                foreach ([$buzzRepository, $widgetRepository] as $index => $repository) {
                    $this->assertSame($repository, $actions->item($index)->getAttribute('href'));
                    $this->assertStringContainsString('btn-primary', $actions->item($index)->getAttribute('class'));
                }
            }
        }
    }

    public function test_saola_boot_registers_every_page_in_both_languages(): void
    {
        $this->get('/vn.html')->assertNotFound();
        $this->get('/about')->assertNotFound();
        $this->get('/vn/about.html')->assertOk()->assertSee('href="/vn/about.html#team"', false);
        $html = $this->get('/')->assertOk()->getContent();
        preg_match('/window\.APP_CONFIGS=(.*?)<\/script>/s', $html, $match);
        $boot = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('#app-root', $boot['container']);
        $this->assertSame('en', $boot['i18n']['locale']);
        $this->assertArrayHasKey('en', $boot['i18n']['messages']);
        $this->assertArrayHasKey('vi', $boot['i18n']['messages']);
        $routes = array_column($boot['router']['routes'], 'component', 'path');
        foreach ($this->pages() as $path => [$locale, $slug, $view]) {
            $this->assertSame('web.themes.default.views.modules.home.'.$view, $routes[$path]);
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
            ->assertSee('href="/fr/products.html"', false)
            ->assertDontSee('site.home.hero.title');
        $this->assertSame('/fr/products/saola.html', Site::url('products/saola', 'fr'));
        $this->assertSame(['fr', 'products/saola'], Site::resolve('/fr/products/saola.html'));
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
            $this->assertSame('https://saolabs.org/'.($slug ? $slug.'.html' : ''), $alternates['en']);
            $this->assertSame('https://saolabs.org/vn'.($slug ? '/'.$slug.'.html' : ''), $alternates['vi']);
        }
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://saolabs.org/sitemap.xml');
        $this->get('/up')->assertOk();
        $this->get('/missing-page')->assertNotFound();
    }
}
