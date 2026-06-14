# Design Decisions — Invia.vn

Ghi lại các quyết định thiết kế quan trọng để tra cứu sau.

---

## 1. Template Showcase — Không Dùng Data Thật

**Quyết định:** Không cho host publish thiệp thật làm case study.

**Lý do:**
- Danh sách khách chứa tên, SĐT, email — rất nhạy cảm
- Lời chúc là tin nhắn cá nhân
- Rủi ro privacy cao, không đáng

**Giải pháp:** Admin tạo data mẫu cho từng template → showcase design thuần túy, không dùng data host thật. Admin kiểm soát hoàn toàn nội dung showcase.

---

## 2. Lưu Trữ Data — Auto-delete + Premium Vĩnh Viễn

**Quyết định:** Auto-delete theo thời hạn, lưu vĩnh viễn là tính năng Premium.

| Loại data | Thời hạn mặc định |
|---|---|
| Lời chúc (text) | 6 tháng sau ngày sự kiện |
| Thiệp, RSVP, danh sách khách | 1 năm sau ngày sự kiện |
| Gói Premium | Lưu trữ vĩnh viễn |

---

## 3. Nhạc Nền Thiệp — Hybrid + Safe Harbor

**Quyết định:** 2 lựa chọn cho host:
1. Chọn từ thư viện nhạc royalty-free do admin curate sẵn (~30–50 bài)
2. Tự upload MP3 (tối đa 5MB) — host tự chịu trách nhiệm bản quyền

**Safe Harbor:** Host bắt buộc tick checkbox xác nhận quyền sử dụng trước khi upload. Invia.vn ghi rõ trong ToS không chịu trách nhiệm về nhạc do user upload. Có quy trình gỡ khi nhận khiếu nại bản quyền.

**Lý do không dùng Spotify/YouTube:** Spotify yêu cầu listener có tài khoản, YouTube ToS cấm audio-only và hiển thị quảng cáo — cả 2 đều không phù hợp làm nhạc nền thiệp.

**SoundCloud embed:** Invia.vn không chịu bản quyền khi embed — SoundCloud tự xử lý với rights holder. Rủi ro duy nhất là track bị gỡ → thiệp mất nhạc. Hiển thị cảnh báo cho host khi nhập link SoundCloud.

**Nguồn nhạc royalty-free cho thư viện:** Pixabay Music, YouTube Audio Library, Free Music Archive — miễn phí, đủ dùng cho MVP. Scale lên mới thuê nhạc sĩ VN sáng tác riêng.

---

## 4. Upload Media — Bỏ Khỏi MVP

**Quyết định:** Invitee không được upload ảnh/video kèm lời chúc ở MVP.

**Lý do:** Phức tạp — cần validate mime type, resize, storage management, auto-delete, tốn dung lượng VPS.

**Roadmap:** Xem xét lại ở Phase 2 hoặc gói Premium.

---

## 5. Google OAuth — Primary Auth cho Inviter

**Quyết định:** Google OAuth là phương thức đăng ký/đăng nhập chính cho host.

**Lý do:** Host là cá nhân, lười điền form — Google login 2 click, giảm drop-off. Email/Password giữ làm secondary.

**Invitee:** Không có tài khoản — truy cập qua unique token trong link thiệp.

---

## 6. Template Deploy — File-based qua Git + config.json

**Quyết định:** Template là Blade + CSS/JS + config.json + assets/, deploy qua git — không upload qua admin UI.

**Lý do:** An toàn hơn, version control rõ ràng, không có rủi ro XSS từ HTML string trong DB.

**Admin UI** chỉ quản lý record trong DB (tên, blade_file reference, thumbnail, giá, version).

**config.json** định nghĩa các slot editable (text, image, video) — Vue drag editor đọc file này để render đúng panel tùy chỉnh.

---

## 7. Storage — Laravel Filesystem Abstraction từ Đầu

**Quyết định:** Dùng `Storage::disk()` từ MVP, không hardcode path trực tiếp.

**Lý do:** Khi scale lên Cloudflare R2 hoặc S3 chỉ cần đổi `.env`, không sửa code business logic.

**Roadmap:**
- MVP → VPS local (`FILESYSTEM_DISK=local`)
- ~1,000 event → Cloudflare R2 (~$0.015/GB/tháng)
- Scale lớn → R2 + Managed MySQL

---

## 9. Đa Ngôn Ngữ — Không Làm MVP, Code i18n-ready

**Quyết định:** MVP chỉ hỗ trợ tiếng Việt. Không implement đa ngôn ngữ cho đến khi có nhu cầu thực tế.

**Lý do:** Target market là VN, 99% thiệp cưới VN dùng tiếng Việt. Effort cao, value thấp ở giai đoạn này.

**Code i18n-ready ngay từ đầu:** Dùng Laravel Lang files, không hardcode string trong code.
```php
// Luôn dùng
__('rsvp.confirm')

// Không hardcode
'Xác nhận tham dự'
```

Khi cần thêm English sau chỉ tạo `lang/en/` — không cần refactor code.

**Roadmap:** Xét thêm English ở Phase 3+ nếu có nhu cầu thực tế (Việt Kiều, khách quốc tế).

---

## 10. Notification — In-app + Email

**Quyết định:** Cả 2 — in-app (chuông dashboard) và email.

**In-app:** Bảng `notifications` trong DB, dashboard poll mỗi 30s hoặc dùng Laravel Echo khi scale. Badge đỏ trên chuông, đánh dấu đã đọc.

**Email:** Gửi qua Resend.com đồng thời khi tạo notification.

**Trigger:** Khách RSVP, khách gửi lời chúc, thông báo hệ thống từ admin.

---

## 11. Shared Dashboard — Full Access + Read-only

**Quyết định:** Hỗ trợ cả 2 cấp quyền từ đầu.

| Permission | Có thể làm |
|---|---|
| `readonly` | Xem khách, RSVP, lời chúc |
| `full` | Xem + thêm/sửa/xóa khách, quản lý RSVP, ẩn lời chúc |

Host thu hồi quyền bất cứ lúc nào. Lưu trong `event_collaborators.permission`.

---

## 12. Blog — Tự Build

**Quyết định:** Tự build blog trong Laravel để kiểm soát SEO hoàn toàn.

**Lý do:** Dùng tool ngoài (Notion, WordPress headless) mất kiểm soát URL structure, meta tags, sitemap. Tự build = Blade render server-side → Google index đầy đủ.

**Schema:** `blog_posts` (title, slug, content HTML, excerpt, og_image, published_at). Admin viết bài qua dashboard.

---

## 13. Database Engine — PostgreSQL

**Quyết định:** Dùng PostgreSQL thay vì MySQL.

**Lý do:** `events.settings` và `notifications.data` là JSONB — PostgreSQL query/index trực tiếp vào JSON key nhanh hơn MySQL JSON type. Long-term tốt hơn khi scale.

**Laravel config:** `DB_CONNECTION=pgsql` — Eloquent hỗ trợ đầy đủ, không cần thay đổi code.

---

## 14. Database — Repository Pattern

**Quyết định:** Dùng Repository Pattern từ đầu để trừu tượng hóa data layer.

```
Controller → Service → Repository → DB
```

**Lý do:** Sau này swap MySQL sang managed DB hoặc đổi driver không cần sửa business logic. Dễ test hơn.
