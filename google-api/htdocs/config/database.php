<?php
$servername = "sql101.infinityfree.com"; // Địa chỉ máy chủ MySQL
$username = "if0_37931850"; // Tên người dùng MySQL
$password = "jQ3HhzhGd0"; // Mật khẩu MySQL
$dbname = "if0_37931850_truyentranhnet"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập mã hóa cho kết nối
$conn->set_charset("utf8mb4");
?>