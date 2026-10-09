# SaoLabs

Website giới thiệu toàn bộ hệ sinh thái SaoLabs, sử dụng Laravel 13, Saola Core,
Saola Compiler, Saola Client, TypeScript và Vite theo cấu trúc của NeverSEO.

| Ngôn ngữ | URL |
| --- | --- |
| English | `/` |
| Tiếng Việt | `/vn` |

Nội dung giới thiệu SaoLabs là nhóm phát triển phần mềm độc lập tại Việt Nam;
bao gồm Saola Framework, NeverSEO, Crazify, giải pháp phần mềm/AI, đội ngũ và liên hệ.
Mỗi sản phẩm có trang giới thiệu riêng trên SaoLabs; từ đó dẫn tới repo GitHub
của Saola/Crazify hoặc website chính `https://neverseo.com`.
Chưa có chức danh hoặc ảnh thành viên được cung cấp nên giao diện dùng tên và chữ cái đại diện.

## Cấu trúc website

Website có 7 trang cho mỗi ngôn ngữ, tổng cộng 14 URL công khai:

| Nội dung | English | Tiếng Việt |
| --- | --- | --- |
| Trang chủ | `/` | `/vn` |
| Hệ sinh thái | `/products` | `/vn/products` |
| Saola Framework | `/products/saola` | `/vn/products/saola` |
| NeverSEO | `/products/neverseo` | `/vn/products/neverseo` |
| Crazify | `/products/crazify` | `/vn/products/crazify` |
| Giới thiệu và đội ngũ | `/about` | `/vn/about` |
| Liên hệ | `/contact` | `/vn/contact` |

Menu chính: Sản phẩm, Về SaoLabs, Liên hệ. Trang chủ tập trung vào định vị,
sơ đồ hệ sinh thái, ba sản phẩm kèm đối tượng sử dụng, năng lực phát triển,
hình giao diện thực tế, cách làm việc, giới thiệu đội ngũ, FAQ và lời mời liên hệ.
Trang About giới thiệu đội ngũ đầy đủ; trang Contact chứa toàn bộ thông tin liên hệ.
Mỗi URL có một H1, title, description, canonical riêng và cặp hreflang en/vi tương ứng.
Danh mục route dùng chung cho Laravel và sitemap nằm trong `config/site.php`.

## Thiết kế giao diện

Giao diện được làm lại dựa trên mẫu `templates/cartzilla/index.html` và
`templates/cartzilla/about-v2.html`: nền trắng, typography rõ ràng, màu đỏ hồng,
khối sản phẩm bo góc lớn, phần giải pháp, giới thiệu, đội ngũ và liên hệ đồng nhất.
Dùng trực tiếp CSS và icon Cartzilla được cung cấp trong repo, sao chép vào
`public/assets/cartzilla/`; không cần tải template từ mạng khi chạy website.

- Tiếng Anh dùng Inter của template; tiếng Việt dùng Roboto local để đủ dấu và đồng nhất nét chữ.
- Logo chữ S kế thừa từ Saola, đổi màu nhấn theo giao diện.
- NeverSEO dùng ảnh giao diện thực tế từ repo NeverSEO.
- Saola và Crazify dùng khối minh họa HTML/CSS, không hiển thị số liệu giả.
- Đội ngũ dùng chữ cái đại diện vì chưa có ảnh hoặc chức danh được xác nhận.
- Chi tiết nguồn asset và quy tắc chỉnh sửa: [docs/DESIGN.md](docs/DESIGN.md).

## Phát triển trong workspace

Các package `core`, `compiler`, `client`, `builder` phải nằm cùng cấp với `saolabs`.
Yêu cầu PHP 8.3+, Composer 2, Node.js 20+.

```bash
composer install
npm ci
cp .env.example .env       # Chỉ khi chưa có .env
php artisan key:generate
npm run build:views
npm run dev
```

Website: `http://localhost:8004`. Vite: `http://localhost:5175`.
Không cần database cho trang giới thiệu; session/cache dùng file.

```bash
docker compose up --build
```

Docker dùng cổng web `8004`, Vite HMR `5177`.

## Nguồn và kiểm tra

- Nguồn 7 trang dùng chung cho mọi ngôn ngữ: `resources/saola/web/views/modules/home/*.sao`.
- Một header/footer chung: `resources/saola/web/views/layouts/site.sao`.
- Nội dung và metadata: `lang/en/site.php`, `lang/vi/site.php`, gọi qua `__()` của Laravel.
- Locale, prefix URL và tên ngôn ngữ: `config/site.php`; `vi` dùng prefix `vn`.
- Giao diện chung: `public/assets/css/site.css`.
- Menu di động/chuyển ngôn ngữ: `public/assets/js/site.js`.
- SSR shell, metadata và JSON-LD: `resources/views/_system/page/begin.blade.php`.
- Route: `app/Modules/Home/ModuleServiceProvider.php`.
- Blade `resources/views/web`, JS `resources/js/saola` và bundle `public/static/saola` là output sinh tự động; không sửa trực tiếp.

```bash
php artisan test
npm run build           # Compile .sao + typecheck + production bundle
composer validate --no-check-publish
```

Trang render sẵn nội dung để crawler và người dùng không có JavaScript đọc được,
sau đó Saola hydrate trong trình duyệt. Chuyển ngôn ngữ tải HTML đúng URL,
giữ section hash, đồng bộ ngôn ngữ, metadata và dữ liệu có cấu trúc.
Menu hỗ trợ bàn phím, Escape, trạng thái mở/đóng; giao diện hỗ trợ reduced motion.

## Đa ngôn ngữ Laravel

Giao diện không nhân bản theo locale: 7 view trang và 1 layout chung, dù có 2 hay
100 ngôn ngữ. Các chuỗi hiển thị, nhãn trợ năng và metadata dùng `__('site.…')`;
HTML nằm trong view, bản dịch chỉ chứa nội dung. Tên sản phẩm/thành viên giữ nguyên.

Key chia theo **trang → section → mục → trường**, dùng tên ngắn theo vai trò
thay vì lấy cả câu hiển thị làm key. Ví dụ trong `lang/vi/site.php`:

```php
'home' => [
    'hero' => [
        'title' => 'Nền tảng phần mềm.',
        'highlight' => 'Ứng dụng AI.',
    ],
    'faq' => [
        'products' => [
            'question' => '…',
            'answer' => '…',
        ],
    ],
],
```

View gọi `__('site.home.hero.title')` hoặc `__('site.home.faq.products.answer')`.
Các nhóm chính: `meta`, `navigation`, `layout`, `common`, `home`, `products`,
`about`, `contact`. Chỉ đưa nội dung dùng chung giữa các trang vào `common`;
chi tiết sản phẩm nằm dưới `products.saola`, `products.neverseo`, `products.crazify`.
Mọi locale giữ cùng cấu trúc key; sửa câu chữ không cần đổi tên key.

Thêm ngôn ngữ, ví dụ tiếng Pháp:

1. Thêm `lang/fr/site.php` với cấu trúc key tương ứng bản tiếng Anh.
2. Thêm `'fr' => ['prefix' => 'fr', 'label' => 'Français', 'og' => 'fr_FR']`
   vào `site.locales` trong `config/site.php`.
3. Chạy `npm run build`, `php artisan test` và `npm run dist:static`.

Không tạo `index-fr.sao`, không sao chép layout và không sửa từng route. Danh mục
locale tự sinh route, liên kết ngôn ngữ, canonical, hreflang và sitemap.
Prefix URL phải duy nhất; một locale dùng prefix rỗng làm trang gốc.
`APP_FALLBACK_LOCALE=en` dùng tiếng Anh khi thiếu key dịch. Với bản dịch chưa hoàn
thiện, cần kiểm tra fallback trước khi công bố URL của ngôn ngữ đó.

Laravel quyết định locale theo URL trong controller trước khi render. SSR boot
chỉ gửi catalog hiện tại và fallback để hydrate; không tải mọi bản dịch của các
ngôn ngữ khác trong mỗi request. Bundle static dùng khả năng đọc `lang/` sẵn có
của Saola Builder; provider khởi tạo locale từ URL trước lần render đầu tiên.
`site_url`, `site_locale`, `site_slug`, `site_languages` có bản PHP và client tương
ứng, dùng cùng `site.locales`; đổi ngôn ngữ vẫn tải trang đúng URL và giữ hash.

## Triển khai

### Laravel

Đặt `APP_URL=https://saolabs.org`, `APP_ENV=production`, `APP_DEBUG=false`.
Chạy `composer install --no-dev --optimize-autoloader` và `npm ci && npm run build`.
Web server trỏ document root vào `public/`, cho phép ghi `storage/` và `bootstrap/cache/`.
Giữ APP_KEY riêng cho môi trường triển khai. `/sitemap.xml`, `/robots.txt`, canonical,
hreflang và JSON-LD dùng `APP_URL`, không dùng Host hoặc query của request.

### Static hosting

```bash
npm run dist:static
```

Upload nội dung `dist-static/web/` vào document root của static host. Tiếng Việt
được xuất thành `vn/index.html`. Origin mặc định `https://saolabs.org` được khai báo
trong `sao.config.json`; đổi giá trị này nếu dùng tên miền khác.
Build static tự phát hiện 14 route công khai và đóng gói assets đi kèm.
Script export đọc manifest để khôi phục JSON-LD, giữ hreflang tuyệt đối, kiểm tra
nội dung prerender và tạo sitemap đầy đủ; không sửa package builder dùng chung.
Website chưa được đưa lên hosting hoặc push GitHub trong lần khởi tạo này.
