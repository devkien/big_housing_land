# Big Housing Land - PHP Project

Đây là một ứng dụng web quản lý bất động sản được xây dựng bằng PHP thuần theo kiến trúc gần giống mô hình MVC (Model-View-Controller).

## Cấu trúc thư mục

Dự án được tổ chức theo cấu trúc thư mục rõ ràng để dễ dàng quản lý và phát triển.

```
Big_Housing_Land/
├── app/
│   ├── Controllers/  # Chứa các file xử lý logic, điều hướng
│   │   ├── AuthController.php
│   │   ├── AdminController.php
│   │   ├── MainController.php
│   │   └── SuperAdminController.php
│   │
│   ├── Helpers/      # Chứa các hàm hỗ trợ
│   │   └── functions.php
│   │
│   ├── Models/       # Chứa các file tương tác với cơ sở dữ liệu
│   │   └── User.php
│   │
│   └── Views/        # Chứa các file giao diện (HTML/PHP)
│       ├── admin/
│       ├── auth/
│       ├── main/
│       ├── superadmin/
│       └── partials/
│
├── config/           # Chứa các file cấu hình
│   ├── database.php  # Cấu hình kết nối CSDL
│   └── roles.php     # Định nghĩa các vai trò người dùng
│
├── core/             # Chứa các lớp lõi của ứng dụng
│   ├── Auth.php
│   ├── Controller.php
│   ├── Database.php
│   ├── Middleware.php
│   ├── Model.php
│   └── Router.php
│
├── public/           # Thư mục gốc của web server (Document Root)
│   ├── css/
│   ├── js/
│   ├── icon/
│   ├── images/
│   ├── uploads/      # Thư mục chứa các file được tải lên
│   └── index.php     # Điểm khởi đầu của mọi request
│
└── README.md         # File này
```

### Giải thích chi tiết

*   **`app/`**: Chứa toàn bộ mã nguồn chính của ứng dụng.
    *   **`Controllers`**: Xử lý các yêu cầu (request) từ người dùng, tương tác với `Models` để lấy dữ liệu và truyền dữ liệu đó cho `Views` để hiển thị.
    *   **`Models`**: Đại diện cho cấu trúc dữ liệu. Các lớp trong này chịu trách nhiệm tương tác với cơ sở dữ liệu (truy vấn, thêm, sửa, xóa).
    *   **`Views`**: Chịu trách nhiệm hiển thị dữ liệu cho người dùng. Đây là các file PHP/HTML.
    *   **`Helpers`**: Chứa các hàm tiện ích có thể được sử dụng ở bất kỳ đâu trong ứng dụng (ví dụ: hàm xử lý CSRF token).

*   **`config/`**: Thư mục chứa các file cấu hình cho ứng dụng.
    *   `database.php`: Cấu hình thông tin kết nối đến MySQL (host, tên database, username, password).
    *   `roles.php`: Định nghĩa các hằng số cho vai trò người dùng (user, admin, super_admin) để quản lý phân quyền.

*   **`core/`**: Các thành phần cốt lõi, nền tảng của framework tự xây dựng.
    *   `Router.php`: Phân tích URL của request và quyết định `Controller` và `action` nào sẽ xử lý nó.
    *   `Controller.php`: Lớp Controller cơ sở mà tất cả các controller khác kế thừa.
    *   `Model.php`: Lớp Model cơ sở.
    *   `Database.php`: Quản lý việc kết nối đến cơ sở dữ liệu (sử dụng PDO và mẫu Singleton).
    *   `Auth.php` & `Middleware.php`: Xử lý các vấn đề liên quan đến xác thực (đăng nhập) và phân quyền (kiểm tra vai trò).

*   **`public/`**: Thư mục duy nhất được truy cập công khai từ trình duyệt.
    *   `index.php`: Là **Front Controller**, tất cả các request đều đi qua file này đầu tiên. Nó khởi tạo router và điều phối request.
    *   `css/`, `js/`, `images/`, `icon/`: Chứa các tài nguyên tĩnh như stylesheet, script, và hình ảnh.
    *   `uploads/`: Nơi lưu trữ các file do người dùng tải lên (ví dụ: ảnh đại diện, ảnh CCCD).

## Luồng hoạt động của một Request

1.  Mọi request từ trình duyệt đều được server chuyển hướng đến file `public/index.php`.
2.  `public/index.php` khởi tạo đối tượng `Router`.
3.  Các định tuyến (routes) được định nghĩa trong `public/index.php` để ánh xạ một URL và phương thức HTTP (GET/POST) tới một `action` trong một `Controller` cụ thể.
4.  `Router` phân tích URL hiện tại và tìm ra định tuyến phù hợp.
5.  `Middleware` được thực thi (nếu có) để kiểm tra quyền truy cập (ví dụ: `auth` yêu cầu đăng nhập, `role:admin` yêu cầu quyền admin).
6.  Nếu `Middleware` cho phép, `Router` sẽ khởi tạo đối tượng `Controller` tương ứng và gọi `action` (phương thức) đã được định nghĩa.
7.  Bên trong `action` của `Controller`:
    *   Nó có thể gọi các phương thức tĩnh từ `Model` (ví dụ: `User::findById()`) để lấy dữ liệu từ CSDL.
    *   Sau khi có dữ liệu, nó gọi phương thức `view()` để tải file `View` tương ứng và truyền dữ liệu sang cho View.
8.  File `View` nhận dữ liệu và hiển thị ra giao diện HTML cho người dùng.

## Hướng dẫn cài đặt

1.  **Clone Repository**: Sao chép mã nguồn dự án về máy của bạn.
2.  **Web Server**: Cấu hình một web server (ví dụ: Apache trong XAMPP) và trỏ `DocumentRoot` vào thư mục `public/` của dự án.
3.  **Database**:
    *   Tạo một cơ sở dữ liệu mới trong phpMyAdmin (hoặc công cụ tương tự).
    *   Mở file `config/database.php` và cập nhật các thông tin `host`, `dbname`, `username`, `password` cho phù hợp với môi trường của bạn.
    *   Import file `.sql` (nếu có) để tạo các bảng cần thiết.
4.  **Truy cập**: Mở trình duyệt và truy cập vào địa chỉ localhost của bạn (ví dụ: `http://localhost/Big_Housing_Land`).

