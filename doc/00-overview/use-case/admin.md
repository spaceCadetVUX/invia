# Use Case: Admin

## 1. Quản Lý User

- Xem danh sách host
- Khóa / mở khóa tài khoản
- Reset mật khẩu cho user
- Xem lịch sử event của từng host

---

## 2. Quản Lý Template

> File Blade/CSS/JS deploy qua git — admin chỉ quản lý record trong DB

- Tạo record template mới (tên, blade_file reference, thumbnail, giá, category)
- Sửa / xóa record template
- Bật / tắt hiển thị template
- Upload thumbnail ảnh đại diện
- Set giá template premium
- Tăng version (trigger browser reload CSS/JS mới sau khi deploy)

---

## 3. Quản Lý Thanh Toán

- Xem lịch sử giao dịch toàn hệ thống
- Xác nhận thanh toán thủ công (nếu cần)
- Hoàn tiền / điều chỉnh gói cho user
- Export báo cáo doanh thu theo tháng, theo gói

---

## 4. Quản Lý Mã Giảm Giá

- Tạo coupon code (% hoặc số tiền cố định)
- Giới hạn số lần dùng, thời hạn hiệu lực
- Bật / tắt coupon
- Xem lịch sử sử dụng từng coupon

---

## 5. Quản Lý Nhạc Library

- Upload bài nhạc royalty-free vào thư viện
- Nhập thông tin: tên bài, tác giả, mood (romantic / classical / acoustic / traditional)
- Bật / tắt bài nhạc
- Xóa bài nhạc

---

## 6. Quản Lý Nội Dung

- Xem / ẩn lời chúc vi phạm được báo cáo
- Quản lý nội dung email template (nội dung email gửi cho khách)
- Đăng thông báo hệ thống (banner bảo trì, tính năng mới cho host)
- Quản lý blog posts (tạo, sửa, xóa, publish)

---

## 7. Thống Kê & Báo Cáo

- Tổng quan hệ thống: số user, số event, doanh thu
- Thống kê theo thời gian (ngày / tuần / tháng)
- Thống kê theo gói (Basic / Pro / Premium)
- Thống kê template phổ biến nhất

---

## 8. Hệ Thống

- Xem log lỗi
- Chạy cleanup event hết hạn thủ công
- Restart queue worker
