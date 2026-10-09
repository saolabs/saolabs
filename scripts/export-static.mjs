import { spawnSync, execFileSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../', import.meta.url));
const configuration = JSON.parse(readFileSync(resolve(root, 'sao.config.json'), 'utf8'));
const origin = new URL(configuration.contexts.web.dist.static.origin).origin;
const build = spawnSync(resolve(root, 'node_modules/.bin/sao-dist'), ['web', '--static'], {
    cwd: root,
    env: { ...process.env, APP_URL: origin },
    stdio: 'inherit',
});
if (build.error) throw build.error;
if (build.status !== 0) process.exit(build.status ?? 1);

const siteConfiguration = JSON.parse(execFileSync(process.env.SAOLA_PHP_BINARY || 'php', [
    '-r', 'echo json_encode(require $argv[1]);', resolve(root, 'config/site.php'),
], { encoding: 'utf8' }));
const languages = Object.entries(siteConfiguration.locales);
const defaultLocale = languages.find(([, language]) => language.prefix === '')[0];
const localizedUrl = (slug, locale) => '/' + [siteConfiguration.locales[locale].prefix, slug].filter(Boolean).join('/');
const routeInfo = route => {
    const path = route.replace(/^\/+|\/+$/g, '');
    for (const [locale, { prefix }] of languages) {
        if (prefix && (path === prefix || path.startsWith(prefix + '/'))) return { locale, slug: path.slice(prefix.length).replace(/^\//, '') };
    }
    return { locale: defaultLocale, slug: path };
};
const descriptions = Object.fromEntries(languages.map(([locale]) => {
    const source = resolve(root, `lang/${locale}/site.php`);
    const description = execFileSync(process.env.SAOLA_PHP_BINARY || 'php', [
        '-r', '$catalog = file_exists($argv[1]) ? require $argv[1] : []; echo $catalog["common"]["organization"]["description"] ?? "";', source,
    ], { encoding: 'utf8' });
    return [locale, description];
}));
const output = resolve(root, 'dist-static/web');
const members = ['Lê Ngọc Doãn', 'Phạm Quang Linh', 'Phùng Đức Thắng', 'Vũ Minh Hiếu'];

// The shared static builder omits inline scripts. Restore the site's JSON-LD
// and crawler files here without changing shared packages or generated views.
const manifest = JSON.parse(readFileSync(resolve(output, 'saola-dist.json'), 'utf8'));
if (manifest.skipped?.length) throw new Error('Static export skipped registered pages');
const pages = manifest.pages.map(({ file, url }) => ({
    file, route: url, ...routeInfo(url),
}));
for (const { file, route, locale, slug } of pages) {
    const path = resolve(output, file);
    let html = readFileSync(path, 'utf8');
    if (html.includes('@useBlock(') || (html.match(/<h1(?:\s|>)/g) || []).length !== 1) {
        throw new Error(`Incomplete static content in ${file}`);
    }
    // Static packaging makes ordinary hrefs relative. SEO alternates must stay absolute.
    const canonical = origin + route;
    const alternates = [...languages.map(([code]) => [code, localizedUrl(slug, code)]), ['x-default', localizedUrl(slug, defaultLocale)]];
    for (const [language, alternate] of alternates) {
        html = html.replace(new RegExp(`<link rel="alternate" hreflang="${language}" href="[^"]*">`), `<link rel="alternate" hreflang="${language}" href="${origin}${alternate}">`);
        if (!html.includes(`hreflang="${language}" href="${origin}${alternate}"`)) {
            throw new Error(`Incorrect ${language} alternate in ${file}`);
        }
    }
    if (!html.includes(`rel="canonical" href="${canonical}"`)) {
        throw new Error(`Unexpected canonical in ${file}`);
    }
    // The standalone client replaces the Laravel bundle; discard its stale preload.
    html = html.replace(/^.*<script>if\(location\.protocol.*static\/saola\/web\/js\/app\.web\.js.*<\/script>\s*$/gm, '');
    const schema = {
        '@context': 'https://schema.org', '@type': 'Organization',
        name: 'SaoLabs', url: origin + '/',
        description: descriptions[locale] || descriptions[defaultLocale],
        email: 'support.saolabs@gmail.com', telephone: '+84 382 434 682',
        address: {
            '@type': 'PostalAddress', streetAddress: 'Số 172, đường Bà Triệu, phường Thống Nhất',
            addressRegion: 'Phú Thọ', addressCountry: 'VN',
        },
        member: members.map(name => ({ '@type': 'Person', name })),
    };
    html = html.replace('</head>', `<script type="application/ld+json">${JSON.stringify(schema).replaceAll('<', '\\u003c')}</script>\n</head>`);
    writeFileSync(path, html);
}

const escapeXml = value => value.replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll('<', '&lt;');
const site = escapeXml(origin);
const urls = pages.map(({ route, slug }) => {
    const alternates = [...languages.map(([code]) => [code, localizedUrl(slug, code)]), ['x-default', localizedUrl(slug, defaultLocale)]];
    return `  <url><loc>${site}${escapeXml(route)}</loc>
${alternates.map(([code, path]) => `    <xhtml:link rel="alternate" hreflang="${code}" href="${site}${escapeXml(path)}"/>`).join('\n')}
  </url>`;
}).join('\n');
writeFileSync(resolve(output, 'sitemap.xml'), `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
${urls}
</urlset>\n`);
writeFileSync(resolve(output, 'robots.txt'), `User-agent: *\nAllow: /\nSitemap: ${origin}/sitemap.xml\n`);
console.log(`SaoLabs static site ready: ${output}`);
