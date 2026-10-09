import { defineBundle, SectionManager, ServiceProvider } from '@saolabs/client';

type Language = { prefix: string; label: string; og: string };
const locales = (): Record<string, Language> => (window as any).APP_CONFIGS.view.systemData.__site__.locales;
const resolvePath = (): [string, string] => {
    const path = location.pathname.replace(/^\/+|\/+$/g, '');
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
    return '/' + (language.prefix + '/' + slug.replace(/^\//, '')).replace(/^\/+|\/+$/g, '');
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
        const initializePage = () => {
            const controller = app.View.getCurrentView()?.__ctrl__;
            if (controller) {
                for (const section of controller.sections.values()) {
                    if (section.name.startsWith('meta:') || section.name === 'html:lang') SectionManager.add(section);
                }
                SectionManager.mountViewSections(controller.viewId);
                document.documentElement.lang = String(SectionManager.resolve('html:lang', resolvePath()[0]));
            }
        };
        app.Router.afterEach(initializePage);
        initializePage();
    },
});
