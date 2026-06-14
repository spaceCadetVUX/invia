# Sitemap & Cấu Trúc Trang — Invia.vn

Tổng ~55 trang/endpoint. Blade = server-side render (SEO). Inertia = SPA (không cần SEO).

---

## 1. Public / Marketing (Blade — SEO)

| Trang | URL | Ghi chú |
|---|---|---|
| Landing | `/` | CTA tạo thiệp ngay |
| Template gallery | `/templates` | Index tất cả template |
| Template detail | `/templates/{slug}` | Preview + CTA dùng ngay |
| Mua template premium | `/templates/{slug}/purchase` | Checkout template |
| Bảng giá | `/pricing` | So sánh gói |
| Blog list | `/blog` | SEO content marketing |
| Blog detail | `/blog/{slug}` | Bài viết SEO |
| Giới thiệu | `/gioi-thieu` | |
| Liên hệ | `/lien-he` | |
| Điều khoản sử dụng | `/dieu-khoan` | |
| Chính sách bảo mật | `/chinh-sach-bao-mat` | |

---

## 2. Auth

| Trang | URL | Ghi chú |
|---|---|---|
| Đăng nhập | `/login` | |
| Đăng ký | `/register` | |
| Quên mật khẩu | `/forgot-password` | |
| Reset mật khẩu | `/reset-password` | |
| Google OAuth redirect | `/auth/google` | |
| Google OAuth callback | `/auth/google/callback` | |
| Google OAuth error | `/auth/error` | Account bị block, scope từ chối |
| Xác thực email | `/email/verify` | Thông báo cần verify |
| Xác nhận email | `/email/verify/{id}/{hash}` | Link trong email |

---

## 3. Thiệp Public (Blade — OG tags)

| Trang | URL | Ghi chú |
|---|---|---|
| Xem thiệp | `/thiep/{slug}` | Link chung |
| Xem thiệp cá nhân | `/thiep/{slug}?t={token}` | Hiển thị tên khách |
| Form tự đăng ký | `/thiep/{slug}/dang-ky` | Khách tự điền tên vào DS |
| Đăng ký thành công | `/thiep/{slug}/dang-ky/success` | |
| RSVP thành công | `/thiep/{slug}/rsvp/success` | |
| Thiệp cảm ơn | `/cam-on/{slug}?t={token}` | Host gửi sau sự kiện |
| Add to Calendar | `/thiep/{slug}/calendar.ics` | Download file .ics |
| Hủy nhận email | `/unsubscribe/{token}` | Khách hủy reminder — bắt buộc có |

---

## 4. Dashboard Host (Inertia)

| Trang | URL | Ghi chú |
|---|---|---|
| Tổng quan | `/dashboard` | Danh sách event + stats |
| Danh sách event | `/dashboard/events` | |
| Tạo event | `/dashboard/events/create` | |
| Chi tiết event | `/dashboard/events/{id}` | |
| Drag editor thiệp | `/dashboard/events/{id}/editor` | Kéo thả text, upload ảnh, nhạc |
| Quản lý khách | `/dashboard/events/{id}/guests` | |
| Theo dõi RSVP | `/dashboard/events/{id}/rsvp` | |
| Lời chúc | `/dashboard/events/{id}/wishes` | |
| Gửi thiệp | `/dashboard/events/{id}/send` | Email bulk + copy link |
| Thống kê event | `/dashboard/events/{id}/stats` | |
| Cài đặt event | `/dashboard/events/{id}/settings` | |
| Download backup | `/dashboard/events/{id}/backup` | Trigger generate + download zip |
| Template đã mua | `/dashboard/templates` | Library template host sở hữu |
| Hồ sơ | `/dashboard/profile` | Đổi tên, avatar, mật khẩu |
| Gói & thanh toán | `/dashboard/billing` | Chọn gói, nâng cấp |
| Lịch sử thanh toán | `/dashboard/billing/history` | |

---

## 5. Shared Dashboard (Read-only)

| Trang | URL | Ghi chú |
|---|---|---|
| Xem dashboard chia sẻ | `/shared/{token}` | Wedding planner/MC xem, không cần login |

---

## 6. Thanh Toán

| Trang | URL | Ghi chú |
|---|---|---|
| Thanh toán thành công | `/payment/success` | |
| Thanh toán thất bại | `/payment/failed` | |
| Webhook PayOS | `/payment/webhook` | POST — không phải trang |

---

## 7. Admin (Inertia)

| Trang | URL | Ghi chú |
|---|---|---|
| Dashboard | `/admin` | Tổng quan hệ thống |
| Quản lý user | `/admin/users` | |
| Chi tiết user | `/admin/users/{id}` | |
| Quản lý template | `/admin/templates` | |
| Tạo / sửa template | `/admin/templates/create` | Tạo record DB, file deploy qua git |
| Thư viện nhạc | `/admin/music` | Upload nhạc royalty-free |
| Quản lý thanh toán | `/admin/payments` | |
| Coupon | `/admin/coupons` | |
| Blog | `/admin/blog` | |
| Thống kê hệ thống | `/admin/analytics` | |
| System logs | `/admin/logs` | |

---

## 8. Technical

| | URL | Ghi chú |
|---|---|---|
| Sitemap | `/sitemap.xml` | Auto-generate — marketing pages + template pages |
| Robots | `/robots.txt` | Disallow dashboard, admin, thiệp |
| OG Image | `/og/{slug}.jpg` | Dynamic OG image per event |

---

## 9. Error Pages

| Trang | Ghi chú |
|---|---|
| 404 | Not found |
| 500 | Server error |
| 503 | Maintenance mode |

---

## Tóm Tắt Render Strategy

| Nhóm | Render | Lý do |
|---|---|---|
| Marketing, Blog, Template gallery | Blade | SEO — crawler đọc được |
| Thiệp public | Blade | OG tags cho Facebook/Zalo share |
| Dashboard host | Inertia + Vue | Reactive UI, không cần SEO |
| Admin | Inertia + Vue | Internal tool |
| Auth | Blade (Breeze default) | Simple, không cần reactivity |
