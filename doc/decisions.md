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

## 3. Upload Media — Bỏ Khỏi MVP

**Quyết định:** Invitee không được upload ảnh/video kèm lời chúc ở MVP.

**Lý do:** Phức tạp — cần validate mime type, resize, storage management, auto-delete, tốn dung lượng VPS.

**Roadmap:** Xem xét lại ở Phase 2 hoặc gói Premium.

---

## 4. Google OAuth — Primary Auth cho Inviter

**Quyết định:** Google OAuth là phương thức đăng ký/đăng nhập chính cho host.

**Lý do:** Host là cá nhân, lười điền form — Google login 2 click, giảm drop-off. Email/Password giữ làm secondary.

**Invitee:** Không có tài khoản — truy cập qua unique token trong link thiệp.

---

## 5. Template Deploy — File-based qua Git

**Quyết định:** Template là Blade + CSS/JS static files, deploy qua git — không upload qua admin UI.

**Lý do:** An toàn hơn, version control rõ ràng, không có rủi ro XSS từ HTML string trong DB.

**Admin UI** chỉ quản lý record trong DB (tên, blade_file reference, thumbnail, giá, version).
