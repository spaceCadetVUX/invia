# Invia.vn — Template System

---

## Cấu Trúc File

Blade và CSS/JS **phải tách thư mục** — `resources/views/` không được Nginx serve, browser không load được file CSS/JS trong đó.

```
resources/
└── views/
    ├── layouts/
    │   ├── app.blade.php              ← Dashboard layout (Tailwind + Vite)
    │   └── thiep.blade.php            ← Layout riêng cho thiệp public
    └── thiep/
        ├── show.blade.php             ← Route loader
        └── templates/
            ├── classic/
            │   └── index.blade.php    ← Blade HTML only
            ├── modern/
            │   └── index.blade.php
            └── rustic/
                └── index.blade.php

public/
└── templates/                         ← Static files — Nginx serve trực tiếp
    ├── classic/
    │   ├── style.css                  ← CSS scoped .tmpl-classic
    │   ├── script.js                  ← IIFE + GSAP
    │   ├── config.json                ← Slot definitions cho Vue drag editor
    │   └── assets/                    ← Ảnh trang trí fix cứng (hoa, khung, nền)
    │       ├── bg.jpg
    │       └── flower.png
    ├── modern/
    │   ├── style.css
    │   ├── script.js
    │   ├── config.json
    │   └── assets/
    └── rustic/
        ├── style.css
        ├── script.js
        ├── config.json
        └── assets/
```

**Lý do tách:**
- `resources/views/` → chỉ PHP/Blade, không web-accessible
- `public/templates/` → Nginx serve trực tiếp, browser load được

---

## config.json — Slot Definitions

Mỗi template có 1 file `config.json` định nghĩa các element mà host có thể tùy chỉnh. Vue drag editor đọc file này để render đúng panel.

```json
{
  "slots": {
    "bride_name": { "type": "text",  "default_x": 50, "default_y": 30 },
    "groom_name": { "type": "text",  "default_x": 50, "default_y": 40 },
    "date":       { "type": "text",  "default_x": 50, "default_y": 55 },
    "photo":      { "type": "image", "default_x": 50, "default_y": 20 },
    "video":      { "type": "video", "default_x": 50, "default_y": 70 }
  }
}
```

**Loại slot:**
| Type | Mô tả |
|---|---|
| `text` | Host kéo thả, chỉnh font/màu/size |
| `image` | Host upload ảnh cưới |
| `video` | Host nhập link YouTube/Drive |

**Settings của host** lưu vào `events.settings` (JSON) sau khi edit:

```json
{
  "bride_name": { "x": 48, "y": 32, "font": "Playfair Display", "size": 48, "color": "#fff" },
  "groom_name": { "x": 52, "y": 42, "font": "Playfair Display", "size": 48, "color": "#fff" },
  "date":       { "x": 50, "y": 56, "font": "Cormorant Garamond", "size": 24, "color": "#eee" }
}
```

**Position dùng `%`** — không dùng `px` để responsive trên mọi màn hình.

---

## CSS Isolation — Scoped Wrapper Class

Mỗi template wrap toàn bộ trong class riêng, tránh xung đột giữa các template:

```html
{{-- classic/index.blade.php --}}
<div class="tmpl-classic">
  <div class="hero">...</div>
  <div class="content">...</div>
</div>
```

```css
/* public/templates/classic/style.css */
/* Toàn bộ CSS scoped dưới .tmpl-classic */
.tmpl-classic .hero { ... }
.tmpl-classic .content { ... }
.tmpl-classic h1 { font-family: 'Playfair Display'; }
```

**Quy tắc:** `.tmpl-{tên-template}` — bắt buộc, không ngoại lệ.

---

## JS Isolation — IIFE + Scoped Selector + GSAP

```js
// public/templates/classic/script.js
(function() {
  const root = document.querySelector('.tmpl-classic')
  if (!root) return  // guard — chỉ chạy đúng template

  // GSAP animate bình thường — Blade chỉ render HTML, GSAP chạy trong browser
  gsap.timeline()
    .from(root.querySelector('.hero'),   { opacity: 0, y: 40, duration: 1.2 })
    .from(root.querySelector('.names'),  { opacity: 0, y: 30, duration: 1 }, "-=0.6")
    .from(root.querySelector('.date'),   { opacity: 0, scale: 0.8, duration: 0.8 }, "-=0.4")

  // ScrollTrigger
  gsap.from(root.querySelector('.content'), {
    scrollTrigger: root.querySelector('.content'),
    opacity: 0, y: 30, duration: 0.8
  })
})()
```

- **IIFE** — không leak biến ra global scope
- **Guard `if (!root) return`** — JS chỉ chạy đúng template
- **GSAP target qua `root.querySelector`** — không dùng global selector

---

## Layout Thiệp — Tách Hoàn Toàn Khỏi Dashboard

```blade
{{-- layouts/thiep.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  @yield('og-tags')
  @yield('template-styles')

  {{-- GSAP load ở layout — dùng chung mọi template --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" defer></script>
</head>
<body>
  @yield('content')
  @yield('template-scripts')
</body>
</html>
```

**Không load Tailwind ở đây** — Tailwind là của dashboard, không phải thiệp.
GSAP load ở layout — không cần load lại trong từng template.

---

## Route Loader — show.blade.php (Đã Fix Security)

```blade
{{-- thiep/show.blade.php --}}
@extends('layouts.thiep')

@section('og-tags')
  <meta property="og:title"       content="{{ $event->title }}" />
  <meta property="og:description" content="{{ $event->date }} • {{ $event->venue }}" />
  <meta property="og:image"       content="{{ Storage::url($event->og_image_path) }}" />
  <meta property="og:url"         content="{{ url('/thiep/' . $event->slug) }}" />
  <meta property="og:type"        content="website" />
  <meta name="twitter:card"       content="summary_large_image" />
@endsection

@include('thiep.templates.' . $event->template->blade_file . '.index')
```

**Whitelist bắt buộc trong Controller — không để Blade tự include:**

```php
// app/Http/Controllers/ThiepController.php
public function show(string $slug)
{
    $event = Event::where('slug', $slug)->firstOrFail();

    // Fix path traversal — chỉ cho phép template đã đăng ký trong DB + tồn tại file
    $allowedTemplates = Template::pluck('blade_file')->toArray();
    abort_if(!in_array($event->template->blade_file, $allowedTemplates), 404);

    return view('thiep.show', compact('event'));
}
```

Whitelist lấy từ DB — không hardcode, thêm template mới không cần sửa code.

---

## Load CSS/JS Template — Cache Busting

```blade
{{-- trong index.blade.php của từng template --}}
@section('template-styles')
  <link rel="stylesheet"
    href="{{ asset('templates/' . $template->blade_file . '/style.css') }}?v={{ $template->version }}">
@endsection

@section('template-scripts')
  <script
    src="{{ asset('templates/' . $template->blade_file . '/script.js') }}?v={{ $template->version }}"
    defer></script>
@endsection
```

`?v={{ $template->version }}` — tránh browser cache file cũ khi template update.
Mỗi lần admin cập nhật template → tăng `version` trong DB là browser load file mới.

---

## Deploy Template Mới — Zero Downtime (Đã Fix)

### Atomic Deployment với Symlink

```
/var/www/
├── releases/
│   ├── 20260613_001/    ← old
│   └── 20260613_002/    ← new
├── shared/
│   ├── .env             ← dùng chung mọi release
│   └── storage/         ← dùng chung mọi release
└── current -> releases/20260613_001   ← Nginx trỏ vào đây
```

### GitHub Actions Script — Hoàn Chỉnh

```yaml
script: |
  # 1. Shallow clone — nhanh hơn full clone
  RELEASE="/var/www/releases/$(date +%Y%m%d_%H%M%S)"
  git clone --depth=1 git@github.com:you/thiep.git $RELEASE

  # 2. Link shared files
  ln -sf /var/www/shared/.env $RELEASE/.env
  rm -rf $RELEASE/storage
  ln -sf /var/www/shared/storage $RELEASE/storage

  # 3. Install PHP dependencies
  cd $RELEASE
  composer install --no-dev --optimize-autoloader

  # 4. Build dashboard assets (Tailwind/Vite) — KHÔNG build template CSS/JS
  npm ci && npm run build

  # 5. Cache Laravel
  php artisan config:cache
  php artisan view:cache
  php artisan route:cache

  # 6. Swap symlink — atomic, zero downtime
  ln -sfn $RELEASE /var/www/current

  # 7. Tạo symlink public/storage
  php artisan storage:link

  # 8. Queue restart gracefully — job đang chạy finish xong mới restart
  php artisan queue:restart

  # 9. Giữ 3 release gần nhất
  ls -dt /var/www/releases/*/ | tail -n +4 | xargs rm -rf
```

**Lưu ý:** `npm run build` chỉ compile Tailwind cho dashboard.
Template CSS/JS (`public/templates/*/`) là static file — tracked trong git, không cần build.

### Queue Khi Deploy — Không Mất Job

```
Job đang chạy →
queue:restart được gọi (set flag) →
Job finish bình thường →
Worker tự exit → Supervisor restart → load code mới
```

### Rollback 1 Lệnh

```bash
ln -sfn /var/www/releases/20260613_001 /var/www/current
php artisan queue:restart
```

---

## Tóm Tắt Nguyên Tắc

| Vấn đề | Giải pháp |
|--------|----------|
| CSS/JS không web-accessible | Đặt trong `public/templates/`, Blade ở `resources/views/` |
| CSS template A ảnh hưởng B | Scoped wrapper `.tmpl-*` |
| JS chạy nhầm template | IIFE + guard `if (!root) return` |
| Template xung đột dashboard | Layout `thiep.blade.php` riêng, không có Tailwind |
| Global scope pollution | IIFE bọc toàn bộ JS |
| Path traversal attack | Whitelist từ DB trong Controller, `abort_if` |
| Browser cache file cũ | `?v={{ $template->version }}` query string |
| Downtime khi deploy | Atomic deployment + symlink swap |
| Queue bị kill khi deploy | `queue:restart` — graceful, finish job trước mới restart |
| git clone chậm | `--depth=1` shallow clone |
| Thiếu storage symlink | `php artisan storage:link` sau swap |
