# Feature Spec — Invia.vn

Cấu hình sơ bộ từng tính năng trước khi build. Đọc cùng với `foundation.md` và `tech.md`.

---

## Phase 1 — Core Flow

### F1.1 Tạo Event

**Mô tả:** Host tạo sự kiện mới sau khi đăng nhập.

**Fields:**
| Field | Bắt buộc | Ghi chú |
|---|---|---|
| Tên sự kiện | Có | |
| Loại sự kiện | Có | `wedding` \| `birthday` \| `other` |
| Ngày giờ | Có | |
| Địa điểm | Có | Tên địa điểm, tự generate iframe Maps |
| Mô tả | Không | |

**Rules:**
- Slug auto-generate: `{tên-sự-kiện}-{random 6 ký tự}`, lowercase, no dấu
- Một host free chỉ được 1 event active cùng lúc — nâng gói mới tạo thêm
- Tạo event xong → tự động tạo record `rsvp` trống, redirect vào editor

---

### F1.2 Chọn Template

**Mô tả:** Host chọn template từ danh sách sau khi tạo event.

**Rules:**
- Free: chỉ thấy template `price = 0`
- Basic+: thấy toàn bộ template free
- Template premium: phải mua riêng mới dùng được (check `user_templates`)
- Đổi template sau khi đã tạo → reset `events.settings` về default của template mới, hỏi confirm trước

---

### F1.3 Trang Thiệp Public

**Mô tả:** Blade render server-side, khách truy cập qua link.

**Rules:**
- Whitelist template từ DB — `abort_if` nếu `blade_file` không hợp lệ
- Link chung `/thiep/{slug}` → không hiện tên khách
- Link cá nhân `/thiep/{slug}?t={token}` → validate token trong DB, hiện tên khách
- Token không tồn tại → load trang bình thường như link chung, không báo lỗi
- OG image: dùng `events.og_image_path` nếu có, fallback về ảnh mặc định của template
- Nhạc: auto-load theo `music_type`, nút play nổi góc phải

---

### F1.4 RSVP

**Mô tả:** Khách xác nhận tham dự ngay trên trang thiệp.

**Fields:** `status` (yes/no/maybe), `plus_one` (số người đi kèm), `dietary`

**Rules:**
- Rate limit: 3 lần submit / IP / giờ
- Nếu có token → link RSVP với `guest_id`, cập nhật bản ghi đó
- Nếu không có token → tạo guest mới với `source = 'rsvp_anonymous'`
- Cho phép sửa RSVP: submit lại → `UPDATE` bản ghi cũ (theo token hoặc IP+event trong 24h)
- Sau submit → gửi email xác nhận cho khách (nếu có email)
- Sau submit → tạo notification cho host

---

### F1.5 Lời Chúc

**Mô tả:** Khách gửi lời chúc text trên trang thiệp.

**Rules:**
- Tối đa 500 ký tự
- Rate limit: 1 lời chúc / token / event (nếu có token), 3 lời chúc / IP / giờ
- Sau submit → tạo notification cho host
- Không có tính năng reply hay reaction ở MVP

---

### F1.6 Gửi Email Thiệp

**Mô tả:** Host gửi link thiệp cá nhân cho từng khách qua email.

**Rules:**
- Chỉ gửi cho khách có `email` trong danh sách
- Mỗi email chứa link `/thiep/{slug}?t={token}` riêng của khách đó
- Gửi qua Queue (bulk) — không sync, không block UI
- Cho phép gửi lại cho khách chưa RSVP
- Giới hạn theo gói: Free không có email, Basic+ mới gửi được
- Mỗi email có link `/unsubscribe/{token}` để khách hủy

---

## Phase 2 — Dashboard

### F2.1 Drag Editor

**Mô tả:** Host kéo thả, chỉnh sửa text slot trên thiệp trong dashboard.

**Rules:**
- Load `config.json` của template → render đúng slot
- Position lưu bằng `%` — không dùng `px`
- Sidebar: chọn font (từ danh sách preset Google Fonts), color picker, size slider
- Auto-save mỗi 30s hoặc khi host click Save
- Save → ghi vào `events.settings` (JSONB)
- Slot `image` → upload ảnh cưới (tối đa 5MB, mime check server-side)
- Slot `video` → nhập URL YouTube/Drive
- Nhạc → 3 lựa chọn (xem F1.3)
- Preview real-time trong iframe bên phải editor

---

### F2.2 Quản Lý Khách

**Mô tả:** Host quản lý danh sách khách mời.

**Rules:**
- Nhập tay: form modal inline
- Import Excel: validate header, bỏ qua dòng lỗi, báo tổng kết (X thành công / Y lỗi)
- Template Excel: cung cấp file mẫu download
- Link form tự đăng ký: `/thiep/{slug}/dang-ky` — auto-accept, đếm vào quota gói
- Khi đạt giới hạn gói → block thêm, hiện CTA nâng gói hoặc mua Extra
- Token tự động generate khi tạo khách, 32 ký tự random
- Xóa khách → xóa cả RSVP và lời chúc liên quan

---

### F2.3 Theo Dõi RSVP

**Mô tả:** Host xem trạng thái RSVP của từng khách.

**Rules:**
- Filter: tất cả / đã xác nhận / vắng / chưa phản hồi
- Search: theo tên, email, SĐT
- Sort: theo tên, trạng thái, số bàn
- Export Excel: tất cả hoặc theo filter hiện tại
- Quản lý bàn tiệc (assign khách vào bàn): gói Pro+

---

### F2.4 Lời Chúc Dashboard

**Rules:**
- Hiển thị tất cả lời chúc, mới nhất trên cùng
- Pin: lời chúc pinned luôn hiện trên đầu
- Ẩn: không xóa khỏi DB, chỉ set `is_hidden = true`
- Không có tính năng export lời chúc riêng lẻ (có trong backup zip)

---

### F2.5 Backup & Export

**Mô tả:** Host tải về toàn bộ data sự kiện.

**Rules:**
- Trigger generate → Queue job → khi xong gửi email thông báo link download
- Link download có TTL 24h, sau đó xóa file zip
- 6 files trong zip: `guests.xlsx`, `wishes.xlsx`, `event-summary.pdf`, `wishes-book.pdf`, `thiep.pdf`, `thiep.html`
- `thiep.html` là self-contained (CSS inline, ảnh base64) — mở được offline

---

## Phase 3 — Monetization

### F3.1 Thanh Toán PayOS

**Flow:**
```
Host chọn gói → Laravel tạo payment link (PayOS API)
→ Redirect sang PayOS → Thanh toán
→ PayOS callback webhook → Laravel verify signature
→ Kích hoạt gói / mở khóa template
```

**Rules:**
- Verify webhook signature trước khi xử lý — không tin body thô
- Idempotent: nếu webhook gọi 2 lần → check `payments.gateway_ref`, không activate 2 lần
- Sau activate → gửi email xác nhận cho host
- `payments.event_id` nullable cho trường hợp mua template

---

### F3.2 Giới Hạn Theo Gói

| Gói | Số khách | Email | Export | Bàn tiệc | Lưu trữ |
|---|---|---|---|---|---|
| Free | 50 | Không | Không | Không | 1 năm |
| Basic | 200 | Có | Không | Không | 1 năm |
| Pro | 500 | Có | Có | Có | 1 năm |
| Premium | 1,000 | Có | Có | Có | Vĩnh viễn |
| Extra | +100/lần | — | — | — | — |

**Rules:**
- Check quota trước mỗi action có giới hạn → trả về lỗi rõ ràng + CTA nâng gói
- Gói gắn với `event`, không phải `user` — mỗi event mua gói riêng
- Coupon: apply khi checkout, validate `max_uses` và `expires_at`

---

## Phase 4 — Admin

### F4.1 Admin Panel

**Rules:**
- Route guard: `middleware(['auth', 'role:admin'])`
- Template management: chỉ tạo/sửa record DB — file deploy qua git
- Music library: upload MP3, chỉ admin mới upload được
- Force delete event vi phạm: xóa cả storage files
- Không thể tự xóa tài khoản admin cuối cùng

---

## Edge Cases Chung

| Tình huống | Xử lý |
|---|---|
| Host xóa event đang có khách RSVP | Confirm 2 bước, xóa cascade |
| Khách mở link thiệp sau `expires_at` | Hiện trang "Sự kiện đã kết thúc" |
| Token bị share lên mạng xã hội | Rate limit RSVP per token (3 lần) |
| Upload file vượt size | Báo lỗi client-side + validate server-side |
| PayOS webhook timeout | Queue retry 3 lần với exponential backoff |
| Slug trùng khi generate | Retry với suffix mới, tối đa 5 lần |
