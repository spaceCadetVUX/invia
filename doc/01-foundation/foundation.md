# Foundation Sprints — Invia.vn

Nền móng cần build trước khi làm bất kỳ feature nào. Thứ tự không đổi.

---

## Sprint 0 — Project Setup (1-2 ngày)

- Khởi tạo Laravel 13 + PHP 8.4
- Cấu hình PostgreSQL (`DB_CONNECTION=pgsql`)
- Cài toàn bộ packages:
  ```bash
  composer require laravel/breeze laravel/socialite spatie/laravel-permission
  composer require maatwebsite/excel intervention/image simplesoftwareio/simple-qrcode
  composer require barryvdh/laravel-dompdf payos/payos-php resend/resend-laravel
  npm install gsap @vueuse/core
  ```
- Setup `.env` cơ bản (DB, mail, storage, queue)
- GitHub repo + branch strategy

---

## Sprint 1 — Database (1-2 ngày)

Viết toàn bộ migrations theo đúng thứ tự dependency:

```
users → templates → events → guests → rsvp → wishes
     → music_library
     → user_templates
     → payments → coupons
     → event_collaborators
     → notifications
     → blog_posts
     → system_announcements
```

Không implement logic — chỉ tạo bảng đúng schema, đúng kiểu dữ liệu PostgreSQL:
- `settings`, `data` → `jsonb`
- `token` → unique index
- `slug` → unique index
- `event_id` trong `payments` → nullable

---

## Sprint 2 — Auth (2-3 ngày)

- Laravel Breeze (stack Inertia + Vue)
- Roles với spatie/laravel-permission: `admin`, `host`
- Google OAuth qua Socialite
- Email verification flow
- Middleware guard: `auth`, `role:admin`, `role:host`
- Redirect sau login theo role: admin → `/admin`, host → `/dashboard`

---

## Sprint 3 — Routing & Layouts (1-2 ngày)

Thiết lập đúng kiến trúc Blade vs Inertia từ đầu — **không được nhầm**:

```
/dashboard/*     → Inertia (middleware auth + role:host)
/admin/*         → Inertia (middleware auth + role:admin)
/thiep/*         → Blade  (public, không auth)
/                → Blade  (public marketing)
```

2 layout tách biệt hoàn toàn:
- `layouts/app.blade.php` — dashboard, có Tailwind + Inertia
- `layouts/thiep.blade.php` — thiệp public, có GSAP, **không có Tailwind**

---

## Sprint 4 — Template System (2-3 ngày)

- Tạo 1 template `classic` đầu tiên đúng cấu trúc:
  ```
  public/templates/classic/ → style.css, script.js, config.json, assets/
  resources/views/thiep/templates/classic/ → index.blade.php
  ```
- Route `/thiep/{slug}` → Blade render
- Controller whitelist path traversal
- Cache busting `?v={{ $template->version }}`
- OG tags render server-side

---

## Sprint 5 — Infrastructure (1-2 ngày)

- **Queue:** database driver, `queue:table` migration, Supervisor config
- **Storage:** `Storage::disk()` abstraction, `storage:link`, giới hạn upload
- **Email:** Resend.com driver, test gửi mail
- **i18n:** tạo `lang/vi/` với các key dùng chung (auth, rsvp, errors)
- **Rate limiting:** RSVP throttle `3,60`

---

## Checklist Done

Sau 5 sprint này hệ thống phải:

- [ ] Đăng ký / đăng nhập được (email + Google)
- [ ] Phân quyền đúng (admin vào /admin, host vào /dashboard)
- [ ] Tất cả bảng DB tồn tại đúng schema
- [ ] Mở `/thiep/demo` thấy template classic render đúng
- [ ] OG tags có trong source HTML
- [ ] Queue worker chạy được
- [ ] Gửi email test được qua Resend

Sau đây mới bắt đầu build feature Phase 1.
