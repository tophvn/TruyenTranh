<?php
$servername = "localhost"; // Địa chỉ máy chủ MySQL
$username = "root"; // Tên người dùng MySQL
$password = ""; // Mật khẩu MySQL
$dbname = "truyentranhnet"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

include('../config/send_email.php');
$errors = [];
$message = '';

// Kiểm tra nếu biểu mẫu đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $query = "SELECT user_id FROM users WHERE email = '$email'";
    $result = $conn->query($query); // Thay đổi ở đây

    if ($result && $result->num_rows > 0) {
        // Tạo mã đặt lại mật khẩu
        $token = bin2hex(random_bytes(50));
        $update_query = "UPDATE users SET reset_token = '$token' WHERE email = '$email'";
        if ($conn->query($update_query) === TRUE) { // Thay đổi ở đây
            send_password_reset_email($email, $token);
            $message = 'Thành công! Truy cập Email của bạn để đổi mật khẩu!';
        } else {
            $errors[] = 'Lỗi khi cập nhật token đặt lại mật khẩu.';
        }
    } else {
        $errors[] = 'Email không tồn tại trong hệ thống.';
    }
}

// Đóng kết nối khi không còn sử dụng
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="../img/logo.png" rel="icon">
    <title>Quên Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="../css/css-login-register.css">
</head>
<body>
    <div class="site-wrap d-md-flex align-items-stretch">
        <div class="bg-img" style="background-image: url('../img/forgot-1.jpg')"></div>
        <div class="form-wrap">
            <div class="form-inner">
                <h1 class="title">Quên Mật Khẩu</h1>
                <p class="caption mb-4">Vui lòng nhập địa chỉ email của bạn để đặt lại mật khẩu.</p>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <p><?php echo $message; ?></p>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="pt-3">
                    <div class="form-floating">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                        <label for="email">Địa chỉ Email</label>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary">Đặt Lại Mật Khẩu</button>
                    </div>
                    <div class="mb-2">Quay lại <a href="login.php">Đăng Nhập</a></div>
                </form>
            </div>
        </div>
    </div>
    <a href="../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>
</body>
</html>