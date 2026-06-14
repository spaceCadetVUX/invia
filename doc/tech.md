# Tech Stack — Invia.vn (Website Thiệp Mời Online)

---

## Stack Tổng Quan

| Layer | Công nghệ | Ghi chú |
|-------|-----------|---------|
| Backend | Laravel 13 (PHP 8.4+) | Zero breaking change từ L12 |
| Dashboard (Host) | Inertia.js v2+ + Vue 3 | Reactive UI, không cần build API riêng |
| Trang thiệp public | Blade + GSAP | Bắt buộc — crawler đọc OG tags |
| Styling | Tailwind CSS | |
| Animation | GSAP | |
| Database | MySQL | |
| Queue | Database driver → Redis khi scale | |
| Storage | VPS local | Migrate R2 khi cần |
| Email | Resend.com | |
| Thanh toán | PayOS | |

---

## Kiến Trúc Quan Trọng — Blade vs Inertia

**Đây là quyết định kiến trúc cốt lõi, không được nhầm:**

```
thiep.vn/dashboard/*     → Inertia + Vue 3
                            (host quản lý event, khách, RSVP)

thiep.vn/thiep/{slug}    → Blade + GSAP
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
# Google OAuth
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
users (id, name, email, password, role, created_at)

-- Sự kiện
events (
  id, user_id, type, slug, title, date, venue,
  venue_map_url, settings, og_image_path,
  expires_at, created_at
)

-- Template
templates (id, name, category, thumbnail_path, blade_file, is_active)

-- Khách mời
guests (
  id, event_id, name, email, phone,
  table_no, token, created_at
)

-- RSVP
rsvp (
  id, guest_id, event_id, status,
  plus_one, dietary, created_at
)

-- Lời chúc
wishes (id, guest_id, event_id, message, media_url, created_at)

-- Thanh toán
payments (
  id, user_id, event_id, plan, amount,
  gateway, gateway_ref, status, paid_at
)
```

**Lưu ý schema:**
- `events.slug` — unique, auto-generate từ tên + random suffix
- `guests.token` — unique token 32 ký tự, dùng cho link cá nhân
- `events.expires_at` — ngày hết hạn data (phục vụ auto-delete)
- `events.venue_map_url` — Google Maps URL embed

---

## Packages Laravel

```bash
# Auth
composer require laravel/breeze --dev

# Phân quyền admin/host
composer require spatie/laravel-permission

# Quản lý media upload (ảnh/video lời chúc)
composer require spatie/laravel-medialibrary

# Import/Export Excel danh sách khách
composer require maatwebsite/excel

# Resize & optimize ảnh (thumbnail, OG image)
composer require intervention/image

# QR code trên thiệp
composer require simplesoftwareio/simple-qrcode

# Thanh toán PayOS
composer require payos/payos-php

# Email
composer require resend/resend-laravel
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
| JSON Schema + Vue Renderer | Khi cần editor | Phức tạp, để sau |

**Roadmap:** MVP dùng Blade file → chuyển JSON Schema khi mở editor cho user.

---

## Storage — Ảnh/Video Lời Chúc

**VPS local storage** — đủ dùng vì data không lưu vĩnh viễn.

```env
FILESYSTEM_DISK=local
```

```bash
php artisan storage:link
```

### Giới hạn upload
```
Ảnh: tối đa 5MB
Video: tối đa 50MB, 60 giây
```

### Auto-delete policy
| Loại data | Giữ bao lâu sau ngày cưới |
|-----------|--------------------------|
| Ảnh/video lời chúc | 6 tháng |
| Thiệp, RSVP, danh sách khách | 1 năm |

```php
// Chạy hàng tháng
Schedule::command('events:cleanup')->monthly();
```

**Tính năng Premium:** "Lưu trữ vĩnh viễn" — khi đó mới cần Cloudflare R2.

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
- Gửi reminder 3 ngày trước cưới

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

3,000 free = ~15 đám cưới/tháng — đủ cho MVP. Vượt quota tự động tính, không cần upgrade thủ công.

### Chi phí so với doanh thu
```
Gói Pro 199k = 500 khách → 500 email = $0.20 (~5,000đ) = 2.5% doanh thu
```

### Các email cần gửi
| Trigger | Nội dung |
|---------|---------|
| Host tạo event | Xác nhận + link dashboard |
| Host gửi thiệp | Link thiệp cá nhân (kèm token) |
| Khách RSVP | Xác nhận đã nhận |
| 3 ngày trước cưới | Reminder khách chưa RSVP |

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
- Generate bằng `intervention/image` (MVP) — vẽ text lên ảnh nền
- Chạy qua Queue sau khi host tạo event

### Lưu ý
- Mỗi event một OG image riêng
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
User chọn gói →
Laravel tạo payment link →
Redirect PayOS → User thanh toán (VietQR / ATM / thẻ) →
PayOS webhook → Laravel kích hoạt gói
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
- Hỗ trợ cả nhập tay từng người (thêm khách phát sinh)
- Export danh sách RSVP ra Excel

---

## Link Thiệp — Hybrid Token

```
thiep.vn/thiep/{slug}           → link chung (không tên khách)
thiep.vn/thiep/{slug}?t={token} → link cá nhân (hiển thị tên khách)
```

Cùng 1 Blade view, kiểm tra token để hiện/ẩn tên — không cần 2 route riêng.

---

## Bảo Mật

- **Rate limiting RSVP:** tối đa 3 lần submit/IP/giờ — chống spam
- **Token guest:** 32 ký tự random, không đoán được
- **HTTPS bắt buộc** toàn site
- **File upload validation:** mime type + size check phía server, không tin client

```php
// Rate limit RSVP
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

Host nhập tên địa điểm → hệ thống tự generate embed URL.

---

## Monetization

**Model:** Pay-per-event — phù hợp tâm lý người VN (không thích subscription).

| Gói | Giá | Số khách | Tính năng |
|-----|-----|---------|-----------|
| Free | 0đ | 50 | 1-2 template cơ bản |
| Basic | 99k | 200 | Toàn bộ template, email |
| Pro | 199k | 500 | + Export Excel, quản lý bàn tiệc |
| Premium | 349k | 1,000 | + Video lời chúc, album, lưu trữ vĩnh viễn |
| Extra | +20k | +100 | Mở rộng thêm khách |

---

## Thứ Tự Build

**Phase 1 — Validate ý tưởng (Blade + basic Laravel)**
1. Auth (Breeze)
2. Tạo event + chọn template (2-3 template hardcode)
3. Trang thiệp public Blade + GSAP
4. RSVP form + lời chúc text
5. Email gửi link thiệp (Queue)

**Phase 2 — Host Dashboard (Inertia + Vue)**
1. Danh sách khách + Import Excel
2. Theo dõi RSVP
3. Quản lý lời chúc + media
4. Export Excel

**Phase 3 — Monetization**
1. Tích hợp PayOS
2. Giới hạn theo gói
3. Trang pricing

**Phase 4 — Admin & Scale**
1. Admin panel (thêm template, quản lý user)
2. Analytics
3. Redis Queue khi cần

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
[program:thiep-worker]
command=php /var/www/thiep/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
```
