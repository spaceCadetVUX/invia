# Use Case: Inviter (Gia chủ / Host)

## 1. Tài Khoản

### 1.1 Đăng ký / Đăng nhập
- Đăng nhập bằng Google OAuth (primary)
- Đăng ký / đăng nhập bằng Email + Password (secondary)

### 1.2 Cài đặt tài khoản
- Đổi tên hiển thị, avatar
- Đổi mật khẩu (chỉ áp dụng tài khoản email/password)
- Xóa tài khoản

### 1.3 Notification
- Nhận thông báo (email hoặc in-app) khi khách RSVP
- Nhận thông báo khi khách gửi lời chúc

---

## 2. Dashboard Tổng Quan

- Xem tổng quan sau khi đăng nhập: danh sách event đang hoạt động
- Mỗi event hiển thị: tổng khách, % đã RSVP, đếm ngược ngày sự kiện
- Truy cập nhanh vào từng event

---

## 3. Quản Lý Event

### 3.1 Tạo event mới
- Nhập thông tin: tên sự kiện, loại (cưới / sinh nhật / ...), ngày giờ, địa điểm, mô tả
- Slug tự động generate từ tên + random suffix

### 3.2 Chỉnh sửa event
- Sửa thông tin sau khi tạo (ngày giờ thay đổi, địa điểm đổi, ...)

### 3.3 Xóa / Hủy event
- Xóa event và toàn bộ data liên quan

### 3.4 Duplicate event
- Copy event cũ để tạo nhanh event mới (cùng template, cùng cấu hình)

### 3.5 Nhiều event đồng thời
- Một host có thể tạo và quản lý nhiều event cùng lúc

### 3.6 Chia sẻ quyền quản lý
- Mời wedding planner / MC cùng xem và quản lý dashboard event (read-only hoặc full access)

---

## 4. Template

### 4.1 Xem & chọn template
- Xem danh sách template (free + premium)
- Preview template full-screen trước khi chọn

### 4.2 Mua template premium
- Mua template premium riêng lẻ (ngoài gói sự kiện)
- Xem library template đã mua
- Áp dụng template đã mua vào event

### 4.3 Tùy chỉnh template
- Tùy chỉnh: font, màu sắc, nội dung, ảnh nền
- Đổi template sau khi đã tạo event

### 4.4 Preview thiệp
- Xem trước thiệp như góc nhìn của khách trước khi gửi

---

## 5. Quản Lý Danh Sách Khách

- **Import từ Excel/CSV** — template có sẵn để điền
- **Nhập tay từng người** — cho khách phát sinh
- **Gửi link form đăng ký** — host gửi link, khách tự điền tên vào danh sách (auto-accept, giới hạn theo gói)
- Thông tin khách: Họ tên (bắt buộc), Email, SĐT, Số bàn (tùy chọn)
- Sửa / Xóa khách trong danh sách

---

## 6. Gửi Thiệp

- Gửi email hàng loạt cho khách có email
- Copy link chung → tự share lên Facebook/Zalo group
- Mỗi khách có link cá nhân riêng (unique token)
- Tạo QR code cho thiệp
- Gửi lại cho khách chưa RSVP
- Gửi reminder thủ công (ngoài auto 3 ngày trước)
- Thêm link livestream cho đám cưới hybrid/online

---

## 7. Theo Dõi RSVP

- Xem danh sách khách: đã xác nhận / chưa xác nhận / vắng
- Lọc và tìm kiếm khách theo tên, trạng thái RSVP, số bàn
- Export danh sách RSVP ra Excel
- Quản lý sơ đồ bàn tiệc (gói Pro+)

---

## 8. Quản Lý Lời Chúc

- Xem tất cả lời chúc văn bản, ảnh, video từ khách
- Xem gallery ảnh/video dạng lưới
- Pin lời chúc nổi bật
- Ẩn lời chúc không phù hợp

---

## 9. Sau Sự Kiện

- Gửi thiệp cảm ơn (thank you card) cho khách sau sự kiện
- Download toàn bộ ảnh/video lời chúc
- Xem lại lời chúc sau khi event kết thúc (trong thời hạn lưu trữ)

---

## 10. Thanh Toán

- Chọn gói (Basic / Pro / Premium)
- Mua thêm khách (Extra +100 khách / +20k)
- Mua template premium riêng lẻ
- Thanh toán qua PayOS (VietQR / ATM / thẻ)
- Kích hoạt gói ngay sau thanh toán
- Xem lịch sử thanh toán
