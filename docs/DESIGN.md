# Giao diện SaoLabs

## Hướng thiết kế

Tham khảo hai trang được cung cấp tại `templates/cartzilla/index.html` và
`templates/cartzilla/about-v2.html`. SaoLabs dùng hệ thống nền trắng, màu nhấn
`#f55266`, chữ tối `#181d25`, nền phụ xám nhạt và các card sản phẩm bo góc.
Trang chủ được tổ chức theo thứ tự: định vị và sơ đồ hệ sinh thái, ba sản phẩm
kèm đối tượng sử dụng, bốn nhóm năng lực, hình giao diện thực tế, quy trình ba
bước, giới thiệu đội ngũ, bốn câu hỏi thường gặp, lời mời liên hệ và footer.
Sản phẩm, giới thiệu/đội ngũ và liên hệ có trang riêng cho mỗi ngôn ngữ.
Xem bảng URL đầy đủ tại README. English tại `/`, tiếng Việt tại `/vn`.

## Nguồn asset

| Asset triển khai | Nguồn trong workspace |
| --- | --- |
| `public/assets/cartzilla/css/theme.min.css` | `templates/cartzilla/assets/css/theme.min.css` |
| `public/assets/cartzilla/icons/*` | `templates/cartzilla/assets/icons/` |
| `public/assets/cartzilla/fonts/inter-variable-latin.woff2` | Font Inter của Cartzilla |
| `public/assets/fonts/roboto-variable.ttf` | Font Roboto từ NeverSEO; kèm `ROBOTO-OFL.txt` |
| `public/assets/img/saolabs-mark.svg`, favicon | `../saola/public/static/saola/brand/saola-mark.svg`, đổi màu nhấn |
| `public/assets/img/neverseo-project.jpg` | `../neverseo/public/assets/img/guide/project-home.jpg` |
| `public/assets/img/neverseo-connections.jpg` | `../neverseo/public/assets/img/guide/connections.jpg` |

Cartzilla là template do người dùng cung cấp trong repo. Giữ bản gốc tại
`templates/cartzilla`; các tùy chỉnh nằm trong `public/assets/css/site.css`.
Không cần JavaScript Bootstrap cho giao diện hiện tại; menu được xử lý bởi
`public/assets/js/site.js`. Font, icon và hình ảnh đều phục vụ từ cùng website.

## Chỉnh sửa

Nguồn trang nằm trong `resources/saola/web/views/modules/home/*.sao`. Mỗi trang
chỉ có một view dùng cho mọi locale; layout `layouts/site.sao` chứa header/footer
và điều hướng chung. Nội dung nằm trong `lang/{locale}/site.php`, bao gồm cả
metadata, alt ảnh và nhãn trợ năng. Dùng `__('site.…')`, không viết điều kiện
`locale === 'vi'` cho từng đoạn văn và không đưa HTML vào file dịch.

File dịch dùng mảng lồng nhau theo trang, section, mục và trường. Ví dụ:
`site.home.hero.title`, `site.home.faq.products.answer`,
`site.products.saola.features.rendering.description`. Giữ tên key ngắn và ổn định
như `title`, `description`, `question`, `answer`; không dùng nguyên câu làm key.
`layout` chứa header/footer, `meta` chứa SEO, `common` chứa nội dung dùng chung.
Giữ cấu trúc nhóm giống nhau ở mọi locale; xem ví dụ PHP tại README.

Block `content` dùng tên chữ thường; đặt `@useBlock` trên dòng riêng để compiler
và static renderer đều nhận diện. Trong cú pháp vòng lặp `.sao`, biến lặp dùng
`@foreach (site_languages() as language)` và `language['label']` (không thêm `$`);
compiler tự tạo biến PHP cho Blade và biến JavaScript cho client.

Các tên sản phẩm, tên thành viên, email, điện thoại và địa chỉ phải khớp nhau.
Không sửa file Blade hoặc JS sinh tự động. Sau khi chỉnh `.sao`, chạy
`npm run build`; khi chỉ sửa CSS hoặc ảnh thì tải lại trình duyệt.

Breakpoint chính: menu di động tại 900px; sản phẩm xếp một cột dưới 768px;
đội ngũ xếp một cột trên màn hình nhỏ. Kiểm tra chiều rộng 320px, 390px,
và desktop; không để cuộn ngang. Kiểm tra menu mở/đóng, Escape, liên kết section,
chuyển ngôn ngữ giữ hash và hiển thị ảnh sản phẩm.

Chạy `php artisan test`, `npm run build`, `npm run dist:static` trước triển khai.
Build static tự thu thập CSS, font, icon, ảnh và script theo liên kết trong trang.

## Cấu trúc nội dung và SEO

- `config/site.php`: danh mục slug và view, dùng cho route và sitemap Laravel.
- Mỗi trang khai báo `seo:path`, `html:lang`, `meta:title`, `meta:description`.
- `seo:path` không chứa prefix `vn`; shell tự tạo canonical và cặp hreflang đúng trang.
- Chuyển ngôn ngữ phải đến trang tương ứng, không đưa mọi trang về trang chủ.
- Trang sản phẩm có breadcrumb, đối tượng sử dụng, tính năng, quy trình và sản phẩm liên quan.
- Nội dung trên SaoLabs giới thiệu vai trò sản phẩm; website/repo chính cung cấp phần chuyên sâu.
- Không thêm trang giải pháp hoặc blog rỗng. Chỉ mở rộng khi có nội dung cụ thể.
- Static exporter đọc `saola-dist.json`, kiểm tra H1/nội dung, khôi phục JSON-LD,
  chuẩn hóa hreflang tuyệt đối và xuất sitemap cho mọi trang đã đăng ký.

Khi thêm trang: bổ sung slug/view trong config, tạo một `.sao` với key metadata riêng,
build lại, cập nhật kiểm tra số trang và xác minh trang ở cả Laravel lẫn static.

Thêm ngôn ngữ chỉ cần file dịch và một mục `site.locales`; route, menu ngôn ngữ,
SEO và static export tự dùng danh mục này. Xem hướng dẫn đa ngôn ngữ tại README.
Locale mặc định/fallback lấy từ Laravel; bản static xác định locale từ URL trước
khi render để tránh tiếng Anh ghi đè nội dung tiếng Việt sau khi JavaScript chạy.
