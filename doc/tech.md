# Tech Stack — Invia.vn (Website Thiệp Mời Online)

---

## Stack Tổng Quan

| Layer | Công nghệ | Ghi chú |
|-------|-----------|---------|
| Backend | Laravel 13 (PHP 8.4+) | Zero breaking change từ L12 |
| Dashboard (Host) | Inertia.js v2+ + Vue 3 | Reactive UI, không cần build API riêng |
| Drag Editor | Vue 3 + @vueuse/core | Kéo thả text slot trên thiệp |
| Trang thiệp public | Blade + GSAP | Bắt buộc — crawler đọc OG tags |
| Styling | Tailwind CSS | Dashboard only |
| Animation | GSAP | Thiệp public |
| Database | MySQL | Repository Pattern từ đầu |
| Queue | Database driver → Redis khi scale | |
| Storage | VPS local → Cloudflare R2 | Laravel Filesystem Abstraction |
| Email | Resend.com | |
| Thanh toán | PayOS | |
| PDF Export | barryvdh/laravel-dompdf | Backup thiệp, sách lời chúc |
| Auth OAuth | laravel/socialite | Google OAuth cho host |

---

## Kiến Trúc Quan Trọng — Blade vs Inertia

**Đây là quyết định kiến trúc cốt lõi, không được nhầm:**

```
invia.vn/dashboard/*     → Inertia + Vue 3
                            (host quản lý event, khách, RSVP)

invia.vn/thiep/{slug}    → Blade + GSAP
                            (trang public khách xem thiệp)
```

**Lý do bắt buộc dùng Blade cho trang public:**
- Inertia.js là SPA → crawler Facebook/Google không chạy JS
- OG tags render bằng JS sẽ không được đọc → share link không có ảnh preview
- Blade render server-side → crawler đọc được đầy đủ OG tags

---

## Authentication

**Dùng Laravel Breeze** — nhẹ, đủ dùng, không over-engineer:

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

Chọn stack **Inertia + Vue** khi cài Breeze.

### Phương thức đăng ký / đăng nhập

**Inviter (Host):**
- **Google OAuth** — primary, nút to (giảm drop-off, user lười điền form)
- **Email / Password** — secondary, vẫn giữ cho người không dùng Google

**Invitee (Khách):** Không có tài khoản — truy cập qua token trong link thiệp.

```bash
composer require laravel/socialite
```

- Tạo OAuth credentials miễn phí tại Google Cloud Console
- Lấy: email, họ tên, avatar URL, Google ID
- Email gửi thiệp vẫn qua Resend.com — không phụ thuộc Gmail API

```
Đăng ký / Đăng nhập
─────────────────────
[  Tiếp tục với Google  ]   ← primary

──────── hoặc ────────

Email / Mật khẩu            ← secondary
```

### Roles (dùng spatie/laravel-permission)
| Role | Quyền |
|------|-------|
| `admin` | Quản lý toàn hệ thống, thêm template |
| `host` | Tạo event, quản lý khách của mình |
| `guest` | Không có tài khoản — truy cập qua token |

---

## Database Schema

```sql
-- Người dùng (host)
users (
  id, name, email, password,
  google_id,           -- Google OAuth
  avatar_url,
  role, created_at
)

-- Sự kiện
events (
  id, user_id, template_id, type, slug, title,
  date, venue, venue_map_url,
  settings,            -- JSON: vị trí/font/màu text slots, ảnh, video
  music_type,          -- 'library' | 'soundcloud' | 'upload'
  music_source,        -- URL SoundCloud hoặc path file MP3
  og_image_path,
  expires_at, created_at
)

-- Template
templates (
  id, name, category,
  thumbnail_path,
  blade_file,          -- tên folder template
  version,             -- tăng khi update → cache busting
  price,               -- 0 = free, > 0 = premium (VND)
  is_active,
  created_at
)

-- Khách mời
guests (
  id, event_id, name, email, phone,
  table_no, token,
  source,              -- 'manual' | 'import' | 'self_register'
  created_at
)

-- RSVP
rsvp (
  id, guest_id, event_id,
  status,              -- 'yes' | 'no' | 'maybe'
  plus_one, dietary,
  created_at, updated_at
)

-- Lời chúc
wishes (
  id, guest_id, event_id,
  message,
  is_pinned, is_hidden,
  created_at
)

-- Thư viện nhạc (admin quản lý)
music_library (
  id, title, artist,
  file_path,           -- lưu trong storage
  duration,            -- giây
  mood,                -- 'romantic' | 'classical' | 'acoustic' | 'traditional'
  is_active, created_at
)

-- Template host đã mua
user_templates (
  id, user_id, template_id,
  paid_at
)

-- Thanh toán
payments (
  id, user_id, event_id,
  plan,                -- 'basic' | 'pro' | 'premium' | 'extra' | 'template'
  amount, gateway, gateway_ref,
  status, paid_at
)

-- Mã giảm giá
coupons (
  id, code, type,      -- 'percent' | 'fixed'
  value,
  max_uses, used_count,
  expires_at, is_active,
  created_at
)
```

**Lưu ý schema:**
- `events.slug` — unique, auto-generate từ tên + random suffix
- `guests.token` — unique token 32 ký tự, dùng cho link cá nhân
- `events.expires_at` — ngày hết hạn data (phục vụ auto-delete)
- `events.settings` — JSON lưu toàn bộ tùy chỉnh thiệp (vị trí, font, màu, ảnh, video)
- `events.music_type` — phân biệt 3 nguồn nhạc

---

## Packages Laravel

```bash
# Auth
composer require laravel/breeze --dev

# Google OAuth
composer require laravel/socialite

# Phân quyền admin/host
composer require spatie/laravel-permission

# Import/Export Excel danh sách khách
composer require maatwebsite/excel

# Resize & optimize ảnh (thumbnail, OG image, ảnh cưới)
composer require intervention/image

# QR code trên thiệp
composer require simplesoftwareio/simple-qrcode

# PDF export (backup thiệp, sách lời chúc, tóm tắt sự kiện)
composer require barryvdh/laravel-dompdf

# Thanh toán PayOS
composer require payos/payos-php

# Email
composer require resend/resend-laravel
```

```bash
# Vue drag editor
npm install @vueuse/core
```

---

## Vue Drag Editor — Tùy Chỉnh Thiệp

Host kéo thả text slots trên thiệp trong dashboard. Ảnh trang trí fix cứng theo template.

```
@vueuse/core → useDraggable   ← kéo thả nhẹ, không cần lib nặng
```

**Flow:**
```
Host mở editor → Vue đọc config.json của template
→ Render các text slot có thể kéo thả
→ Sidebar: chỉnh font / màu / size
→ Save → ghi vào events.settings (JSON)
→ Blade đọc settings → render inline style
```

**Position dùng `%`** — không dùng `px` để responsive mọi màn hình.

```js
// Ví dụ lưu settings
{
  "bride_name": { "x": 48, "y": 32, "font": "Playfair Display", "size": 48, "color": "#fff" },
  "groom_name": { "x": 52, "y": 42, "font": "Playfair Display", "size": 48, "color": "#fff" },
  "date":       { "x": 50, "y": 56, "font": "Cormorant Garamond", "size": 24, "color": "#eee" }
}
```

---

## GSAP — Animation

**Website:** https://gsap.com | **License:** Free for commercial (v3.12+)

```bash
npm install gsap
```

```js
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
gsap.registerPlugin(ScrollTrigger)
```

### Ví dụ thiệp cưới
```js
gsap.timeline()
  .from(".ten-co-dau", { opacity: 0, y: 30, duration: 1 })
  .from(".ten-chu-re", { opacity: 0, y: 30, duration: 1 }, "-=0.5")
  .from(".ngay-cuoi",  { opacity: 0, scale: 0.8, duration: 0.8 })
```

### Plugin hữu ích
| Plugin | Dùng cho |
|--------|----------|
| ScrollTrigger | Animation khi scroll |
| TextPlugin | Hiệu ứng gõ chữ |
| Lottie (kết hợp) | Icon động — bông hoa rơi, tim bay (.json) |

**Nguyên tắc:** Thiệp cần elegant, không flashy — tối đa 2-3 animation tinh tế.

---

## Font Chữ

Dùng Google Fonts API trực tiếp trong Blade template:

```html
<link href="https://fonts.googleapis.com/css2?
  family=Playfair+Display:ital@0;1
  &family=Cormorant+Garamond:wght@300;400
  &family=Great+Vibes
  &family=Montserrat:wght@400;500
  &display=swap" rel="stylesheet">
```

| Font | Dùng cho |
|------|---------|
| Playfair Display | Tên cô dâu chú rể |
| Cormorant Garamond | Nội dung thiệp |
| Great Vibes | Chữ thư pháp — điểm nhấn |
| Montserrat | UI dashboard |

---

## Phương Thức Thêm Template

| Cách | Dùng khi | Ghi chú |
|------|---------|---------|
| **File-based Blade** | MVP | Nhanh, đơn giản, đủ dùng |
| DB-based HTML string | Không dùng | XSS risk, khó maintain |
| JSON Schema + Vue Renderer | Khi cần editor nâng cao | Phức tạp, để sau |

**Roadmap:** MVP dùng Blade file → chuyển JSON Schema khi mở editor nâng cao cho user.

### Cấu Trúc File Template

```
public/templates/{name}/
├── style.css        ← CSS scoped .tmpl-{name}
├── script.js        ← IIFE + GSAP
├── config.json      ← định nghĩa các slot editable
└── assets/          ← ảnh trang trí fix cứng (hoa, khung, nền)
    ├── bg.jpg
    └── flower.png

resources/views/thiep/templates/{name}/
└── index.blade.php
```

**`config.json` — định nghĩa slot editable cho Vue drag editor:**

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

Vue editor đọc `config.json` → render đúng panel tùy chỉnh cho từng template. Settings của host lưu vào `events.settings` (JSON).

---

## Nhạc Nền Thiệp

3 lựa chọn cho host:

| Loại | Xử lý | Rủi ro |
|---|---|---|
| Thư viện admin curate | File MP3 lưu trong storage | Zero |
| SoundCloud embed | Iframe embed | Track có thể bị gỡ — hiện cảnh báo |
| Upload MP3 | Lưu storage, tối đa 5MB | Host tự chịu bản quyền — checkbox |

- Nút bật/tắt nhạc nổi góc màn hình (browser block autoplay)
- Nguồn thư viện: Pixabay Music, YouTube Audio Library, Free Music Archive

---

## PDF Export — Backup Sự Kiện

Host tải về 1 file `.zip` gồm 6 file:

| File | Nội dung | Tool |
|---|---|---|
| `guests.xlsx` | Danh sách khách + RSVP | maatwebsite/excel |
| `wishes.xlsx` | Lời chúc dạng bảng | maatwebsite/excel |
| `event-summary.pdf` | Tóm tắt sự kiện layout đẹp | dompdf |
| `wishes-book.pdf` | Sách lời chúc layout đẹp | dompdf |
| `thiep.pdf` | Thiệp cưới bản in | dompdf |
| `thiep.html` | Thiệp cưới responsive + animation | export Blade |

Tác vụ generate zip chạy qua Queue — không block request.

---

## Storage — Kiến Trúc Phân Tầng

### Laravel Filesystem Abstraction

Dùng `Storage::disk()` từ đầu — không hardcode path. Khi scale chỉ đổi `.env`, không sửa code:

```php
Storage::disk('local')->put($path, $content)   // MVP
Storage::disk('r2')->put($path, $content)       // Scale — chỉ đổi config
```

```env
# MVP
FILESYSTEM_DISK=local

# Scale
FILESYSTEM_DISK=r2
AWS_ENDPOINT=https://<account>.r2.cloudflarestorage.com
```

### Roadmap Storage

| Giai đoạn | Storage | Chi phí |
|---|---|---|
| MVP | VPS local | $0 |
| ~1,000 event | Cloudflare R2 | ~$0.015/GB/tháng |
| Scale lớn | R2 + Managed MySQL | Tùy quy mô |

### Auto-delete Policy

| Loại data | Thời hạn |
|---|---|
| Lời chúc text | 6 tháng sau ngày sự kiện |
| Thiệp, RSVP, danh sách khách | 1 năm sau ngày sự kiện |
| Gói Premium | Lưu trữ vĩnh viễn |

```php
Schedule::command('events:cleanup')->monthly();
```

### Giới hạn Upload

```
Ảnh cưới host:   tối đa 5MB
Nhạc nền MP3:    tối đa 5MB
```

---

## Queue

**Driver:** Database (không cần Redis cho MVP)

```bash
php artisan queue:table && php artisan migrate
```

```env
QUEUE_CONNECTION=database
```

### Tác vụ chạy qua Queue
- Gửi email bulk cho khách
- Resize/optimize ảnh sau upload
- Generate OG image sau khi tạo event
- Generate backup zip
- Gửi reminder 3 ngày trước sự kiện

**Upgrade lên Redis** khi > 1,000 event active đồng thời.

---

## Email

**Provider:** Resend.com

```bash
composer require resend/resend-laravel
```

### Bảng giá
| Gói | Giá | Email/tháng |
|-----|-----|-------------|
| Free | $0 | 3,000 |
| Pro | $20/tháng | 50,000 |
| Overage | — | $0.40/1,000 email |

3,000 free = ~15 đám cưới/tháng — đủ cho MVP.

### Các email cần gửi
| Trigger | Nội dung |
|---------|---------|
| Host tạo event | Xác nhận + link dashboard |
| Host gửi thiệp | Link thiệp cá nhân (kèm token) |
| Khách RSVP | Xác nhận đã nhận |
| 3 ngày trước sự kiện | Reminder khách chưa RSVP |

### Lưu ý
- Setup SPF/DKIM trên domain → tránh vào spam
- Gửi qua Queue — không sync
- Mỗi khách có unique token trong link

---

## Social Share — OG Tags

Trang thiệp Blade render server-side → crawler đọc được đầy đủ.

```blade
{{-- resources/views/thiep/show.blade.php --}}
<meta property="og:title" content="{{ $event->title }}" />
<meta property="og:description" content="{{ $event->date }} • {{ $event->venue }}" />
<meta property="og:image" content="{{ Storage::url($event->og_image_path) }}" />
<meta property="og:url" content="{{ url('/thiep/' . $event->slug) }}" />
<meta property="og:type" content="website" />
<meta name="twitter:card" content="summary_large_image" />
```

### OG Image — auto-generate khi tạo event
- Kích thước: **1200×630px**
- Nội dung: tên + ngày cưới + background template
- Generate bằng `intervention/image` — vẽ text lên ảnh nền
- Chạy qua Queue sau khi host tạo event
- Facebook cache ảnh → dùng Facebook Debugger nếu cần clear cache

---

## Thanh Toán

**Cổng chính:** PayOS (hệ sinh thái VNPAY)

```bash
composer require payos/payos-php
```

### Tại sao PayOS?
- Đăng ký online, không cần giấy phép phức tạp
- Hỗ trợ VietQR native
- PHP SDK chính thức
- Phí ~1.1%/giao dịch

### Flow
```
User chọn gói / template premium →
Laravel tạo payment link →
Redirect PayOS → User thanh toán (VietQR / ATM / thẻ) →
PayOS webhook → Laravel kích hoạt gói / mở khóa template
```

### Lưu ý
- Đăng ký tại payos.vn (cần CCCD hoặc GPKD)
- Webhook cần HTTPS → dùng ngrok khi dev local
- MoMo thêm sau làm phương thức phụ

---

## Quản Lý Khách — Import Excel

```bash
composer require maatwebsite/excel
```

### Template Excel cung cấp cho host
| Họ tên | Email | SĐT | Số bàn |
|--------|-------|-----|--------|
| Nguyễn Văn An | an@gmail.com | 0901... | 3 |

- Email và SĐT **không bắt buộc** — host có thể chỉ nhập tên
- Hỗ trợ nhập tay từng người + import Excel + link form tự đăng ký
- Export danh sách RSVP ra Excel

---

## Link Thiệp — Hybrid Token

```
invia.vn/thiep/{slug}           → link chung (không tên khách)
invia.vn/thiep/{slug}?t={token} → link cá nhân (hiển thị tên khách)
```

Cùng 1 Blade view, kiểm tra token để hiện/ẩn tên — không cần 2 route riêng.

---

## Bảo Mật

- **Rate limiting RSVP:** tối đa 3 lần submit/IP/giờ — chống spam
- **Token guest:** 32 ký tự random, không đoán được
- **HTTPS bắt buộc** toàn site
- **File upload validation:** mime type + size check phía server, không tin client
- **Path traversal:** whitelist template từ DB trong Controller

```php
Route::middleware('throttle:3,60')->post('/rsvp', [RsvpController::class, 'store']);
```

---

## Google Maps

Embed địa điểm tiệc — dùng iframe Maps, không cần API key:

```blade
<iframe
  src="https://maps.google.com/maps?q={{ urlencode($event->venue) }}&output=embed"
  width="100%" height="300" loading="lazy">
</iframe>
```

---

## Monetization

**Model:** Pay-per-event — phù hợp tâm lý người VN (không thích subscription).

| Gói | Giá | Số khách | Tính năng |
|-----|-----|---------|-----------|
| Free | 0đ | 50 | 1-2 template cơ bản |
| Basic | 99k | 200 | Toàn bộ template free, email |
| Pro | 199k | 500 | + Export Excel, quản lý bàn tiệc |
| Premium | 349k | 1,000 | + Lưu trữ vĩnh viễn |
| Extra | +20k | +100 | Mở rộng thêm khách |
| Template premium | Tùy template | — | Mua riêng lẻ, dùng vĩnh viễn |

---

## Thứ Tự Build

**Phase 1 — Validate ý tưởng**
1. Auth (Breeze + Google OAuth)
2. Tạo event + chọn template (2-3 template)
3. Trang thiệp public Blade + GSAP
4. RSVP form + lời chúc text
5. Email gửi link thiệp (Queue)

**Phase 2 — Host Dashboard**
1. Vue drag editor (kéo thả text, chỉnh font/màu/size)
2. Upload ảnh cưới + nhạc nền
3. Danh sách khách + Import Excel + link form tự đăng ký
4. Theo dõi RSVP
5. Export Excel + backup zip

**Phase 3 — Monetization**
1. Tích hợp PayOS
2. Giới hạn theo gói
3. Template premium
4. Coupon code

**Phase 4 — Admin & Scale**
1. Admin panel (template, user, nhạc library, coupon)
2. Analytics
3. Redis Queue khi cần
4. Migrate storage lên Cloudflare R2

---

## Deployment

| Thành phần | Lựa chọn |
|-----------|---------|
| VPS | DigitalOcean / Vultr / Linode (4GB RAM đủ cho MVP) |
| Web server | Nginx + PHP-FPM |
| SSL | Let's Encrypt (miễn phí) |
| Process manager | Supervisor (chạy Queue worker) |
| Deploy tool | Laravel Forge hoặc tự setup |

```bash
# Supervisor config cho Queue worker
[program:invia-worker]
command=php /var/www/invia/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
```
