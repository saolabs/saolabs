# Dependencies từ registry

Ứng dụng cài Core và Compiler từ Packagist, Client và Builder từ npm. Lockfile hiện khóa cả bốn thư viện ở **1.0.2**. Không cần các repo `core`, `compiler`, `client` hoặc `builder` bên cạnh ứng dụng.

## Cài cho development

```bash
composer install --prefer-dist --no-interaction
npm ci --include=dev
npm run dev
```

## Cài dependencies và build cho production

Chạy trong thư mục repo đã cấu hình môi trường Laravel:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci --include=dev
npm run build:all
```

Builder, Vite, TypeScript và Tailwind là devDependencies cần ở bước build. Vì vậy bước build dùng `--include=dev` kể cả khi `NODE_ENV=production`. Runtime PHP dùng dependencies đã cài trong `vendor`; frontend dùng bundle đã build trong `public/static/saola`.

Commit `composer.json`, `composer.lock`, `package.json`, `package-lock.json` và `vite.config.js`. Máy triển khai chạy `composer install` và `npm ci` từ lockfile đã commit; không chạy `update` để tự chọn phiên bản mới khi deploy. Việc cấu hình web server, database và workers thực hiện theo hướng dẫn triển khai của ứng dụng.
