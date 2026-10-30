# SaoLabs

Nguồn giao diện web: `resources/saola/web/themes/default/views/`; bootstrap và logic dùng chung giữ tại `web/app/`. `context.manifest.json` khai theme mặc định; `sao.config.json` giữ namespace `web.themes.default.views.*` và mapping tường minh với mode `published`, tương thích pipeline Vite/`dist:static` và dependency đang ghim. Assets công khai tại `public/assets/` giữ nguyên URL.

SaoLabs là nhóm phát triển phần mềm độc lập tại Việt Nam. Chúng tôi cùng nhau tạo dựng những nền tảng web hiện đại và các công cụ làm việc thông minh, hướng đến mục tiêu giản dị: giúp lập trình viên, người làm nội dung và các nhà sáng lập tiết kiệm thời gian, làm việc nhẹ nhàng mà hiệu quả hơn mỗi ngày.

## Tinh thần & Định hướng

Chúng tôi tin rằng phần mềm tốt phải bắt nguồn từ chính sự thấu hiểu công việc thực tế, có kiến trúc vững vàng, mã nguồn trong sáng và giao diện thân thiện với người dùng.

Tại SaoLabs, chúng tôi phát triển các sản phẩm bổ trợ lẫn nhau trong một hệ sinh thái gắn kết. Công nghệ AI được tích hợp một cách có chọn lọc và đúng chỗ — đóng vai trò trợ thủ đắc lực giúp xử lý việc nặng và tự động hóa những khâu lặp lại, trong khi con người luôn là người định hướng và đưa ra quyết định cuối cùng.

## Hệ sinh thái sản phẩm

- **Saola Framework** — Nền tảng full-stack xây dựng trên Laravel, kết hợp tốc độ tải trang tức thì của SSR và trải nghiệm SPA reactive mượt mà, giúp việc làm web hiện đại trở nên nhanh gọn và chuẩn mực.
- **Gehihi** — Hệ thống AI agent để tạo agent không cần lập trình, thêm kỹ năng và tài liệu riêng, thiết lập quy trình, đặt lịch và theo dõi kết quả. [Giới thiệu Gehihi](https://gehihi.vn/) · [Kho agent](https://app.gehihi.vn/ai/agents).
- **NeverSEO** — Không gian làm việc chuyên sâu cho nội dung và SEO: từ nghiên cứu từ khóa, lập dàn ý, sản xuất bài viết chất lượng cho đến tự động xuất bản và theo dõi thứ hạng trên Google & công cụ tìm kiếm AI.
- **Crazify** — Công cụ thông minh giúp bạn bóc tách bài toán, đối chiếu dữ liệu thực tế và biến những ý tưởng còn sơ khai thành kế hoạch hành động từng bước rõ ràng.
- **Buzz! Messenger** — Ứng dụng chat desktop cho Facebook Messenger mang trọn vẹn phong cách Yahoo Messenger hoài niệm: danh sách bạn bè, trọn bộ emoticon kinh điển, tiếng BUZZ rung màn hình và gửi file tiện lợi.
- **Activity Monitor Widget** — Tiện ích native (System Activity) dành riêng cho macOS, theo dõi nhanh CPU, GPU, RAM, nhiệt độ và pin ngay trên menu bar hoặc widget nổi trên màn hình.

Bên cạnh các sản phẩm cốt lõi, SaoLabs cũng nhận tư vấn, phát triển ứng dụng web theo yêu cầu và thiết kế giải pháp tự động hóa quy trình cho các cá nhân, đội ngũ và doanh nghiệp.

## Đội ngũ sáng lập

- Lê Ngọc Doãn
- Phạm Quang Linh
- Phùng Đức Thắng
- Vũ Minh Hiếu

## Liên hệ

- **Website:** [saolabs.org](https://saolabs.org)
- **Email:** [support@saolabs.org](mailto:support@saolabs.org)
- **Điện thoại:** [+84 382 434 682](tel:+84382434682)
- **Địa chỉ:** Số 172, đường Bà Triệu, phường Thống Nhất, tỉnh Phú Thọ, Việt Nam.

Chúng tôi luôn sẵn lòng trò chuyện, lắng nghe phản hồi về sản phẩm và thảo luận các cơ hội hợp tác phát triển.

## Phiên bản và triển khai

Bộ Saola đã nâng ngày 30/10/2026: Core 2.7.0, Compiler 3.0.0, Builder 1.3.3 và Client 1.5.1. Builder và Client dùng tag GitHub chính thức; các lockfile giữ phiên bản dùng để build. View dùng `trans('site.…')` cho bản dịch Laravel, tránh thay đổi ý nghĩa của `__()` trong Compiler 3.0.

Website có sáu sản phẩm và hai ngôn ngữ: Saola Framework, Gehihi, NeverSEO, Crazify, Buzz! Messenger và Activity Monitor Widget. Trang Gehihi ở `/products/gehihi.html` và `/vn/products/gehihi.html`. Danh mục `config/site.php` là nguồn chung cho route và sitemap.

Chạy `npm run build`, `php artisan test` và `npm run dist:static` trước khi triển khai. Tạo `.deploy.env` từ `.deploy.env.example` để cấu hình SSH; tệp này không được commit. `npm run deploy` yêu cầu cây làm việc sạch, lấy commit mới, build và test, sao lưu production, đồng bộ bản tĩnh và kiểm tra mọi URL trong sitemap. Cấu hình Nginx và HTTPS hiện có được giữ nguyên.
