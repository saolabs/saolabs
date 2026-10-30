import { defineBundle, HeadService, SectionManager, ServiceProvider } from '@saolabs/client';

type Language = { prefix: string; label: string; og: string };
const locales = (): Record<string, Language> => (window as any).APP_CONFIGS.view.systemData.__site__.locales;
// The router renders before it commits history; helpers must use the destination.
let activePathname = location.pathname;
const resolvePath = (pathname = activePathname): [string, string] => {
    const path = pathname.replace(/^\/+|\/+$/g, '').replace(/\.html$/, '');
    for (const [locale, { prefix }] of Object.entries(locales())) {
        if (prefix && (path === prefix || path.startsWith(prefix + '/'))) {
            return [locale, path.slice(prefix.length).replace(/^\//, '')];
        }
    }
    const fallback = Object.entries(locales()).find(([, language]) => language.prefix === '')!;
    return [fallback[0], path];
};
const siteUrl = (slug = '', locale = resolvePath()[0]): string => {
    const language = locales()[locale];
    if (!language) throw new Error(`Unsupported locale: ${locale}`);
    const [, path, tail] = slug.match(/^([^?#]*)(.*)$/)!;
    const page = path.replace(/^\/+|\/+$/g, '');
    const suffix = page && !page.endsWith('.html') ? '.html' : '';
    return '/' + (language.prefix + '/' + page + suffix).replace(/^\/+|\/+$/g, '') + tail;
};

// Initialize the URL locale before the first hydration or standalone render.
class SiteLanguageProvider extends ServiceProvider {
    readonly name = 'site.language';
    readonly dependsOn = ['helper'];
    boot() {
        const catalog = (window as any).APP_CONFIGS.i18n;
        this.app.Helper.initTranslations({ ...catalog, locale: resolvePath()[0] });
    }
}

export default defineBundle({
    name: 'web', providers: [SiteLanguageProvider], services: {},
    helpers: {
        site_url: siteUrl,
        site_locale: () => resolvePath()[0],
        site_slug: () => resolvePath()[1],
        site_languages: () => Object.entries(locales()).map(([locale, language]) => ({
            locale, label: language.label, url: siteUrl(resolvePath()[1], locale), current: locale === resolvePath()[0],
        })),
    },
    boot(app) {
        app.Router.beforeEach((_to, _from, path) => {
            activePathname = new URL(path, location.href).pathname;
            const locale = resolvePath()[0];
            if (locale !== app.Helper.getLocale()) {
                app.Helper.initTranslations({ ...(window as any).APP_CONFIGS.i18n, locale });
                app.View.invalidateLocale();
            }
            return true;
        });
        const origin = new URL(document.querySelector<HTMLLinkElement>('link[rel="canonical"]')?.href || location.href).origin;
        let trackedPath = location.pathname + location.search;
        const initializePage = () => {
            activePathname = location.pathname;
            const [locale, slug] = resolvePath();
            const controller = app.View.getCurrentView()?.__ctrl__;
            if (controller) {
                for (const section of controller.sections.values()) {
                    if (section.name.startsWith('meta:') || section.name === 'html:lang') SectionManager.add(section);
                }
                SectionManager.mountViewSections(controller.viewId);
                document.documentElement.lang = String(SectionManager.resolve('html:lang', resolvePath()[0]));
            }
            // The shared shell can survive navigation or resume from the page cache.
            for (const link of document.querySelectorAll<HTMLAnchorElement>('[data-language-link]')) {
                link.href = siteUrl(slug, link.lang);
                link.setAttribute('aria-current', link.lang === locale ? 'page' : 'false');
            }
            for (const link of document.querySelectorAll<HTMLAnchorElement>('.desktop-nav a, #mobile-nav a')) {
                const target = resolvePath(new URL(link.href).pathname)[1];
                const current = target === 'products' ? slug.startsWith('products') : slug === target;
                link.setAttribute('aria-current', current ? (target === 'products' ? 'true' : 'page') : 'false');
            }
            const canonical = origin + siteUrl(slug, locale);
            HeadService.setLink('canonical', canonical);
            HeadService.setMetaProperty('og:url', canonical);
            HeadService.setMetaProperty('og:locale', locales()[locale].og);
            for (const code of Object.keys(locales())) {
                HeadService.setLink('alternate', origin + siteUrl(slug, code), { attrs: { hreflang: code } });
            }
            const defaultLocale = Object.entries(locales()).find(([, language]) => language.prefix === '')![0];
            HeadService.setLink('alternate', origin + siteUrl(slug, defaultLocale), { attrs: { hreflang: 'x-default' } });
            const schema = document.querySelector<HTMLScriptElement>('script[type="application/ld+json"]');
            if (schema) {
                const organization = JSON.parse(schema.textContent || '{}');
                organization.description = app.Helper.__('site.common.organization.description');
                schema.textContent = JSON.stringify(organization);
            }
            const pagePath = location.pathname + location.search;
            if (pagePath !== trackedPath) {
                (window as any).gtag?.('event', 'page_view', {
                    page_path: pagePath, page_location: location.href, page_title: document.title,
                });
                trackedPath = pagePath;
            }
        };
        app.Router.afterEach(initializePage);
        initializePage();
    },
});
