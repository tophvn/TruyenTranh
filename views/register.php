<?php
include('../config/database.php'); 
include '../config/send_email.php'; 
session_start();

$errors = [];
$username = '';
$name = '';
$email = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user'; 

    // Kiểm tra dữ liệu input
    $errors_chars = '/[áàảãạăắằẳẵặâấầẩẫậéèẻẽẹêếềểễệíìỉĩịóòỏõọôốồổỗộơớờởỡợúùủũụưứừửữựýỳỷỹỵđ\s]/i';
    if (preg_match($errors_chars, $username)) {
        $errors['username'] = 'Tên đăng nhập không được chứa dấu hoặc khoảng trắng!';
    }
    if (preg_match($errors_chars, $password)) {
        $errors['password'] = 'Mật khẩu không được chứa dấu!';
    }
    
    // Kiểm tra mật khẩu
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Mật khẩu không trùng khớp!';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif (strlen($password) > 255) { 
        $errors['password'] = 'Mật khẩu không hợp lệ!';
    }

    // Kiểm tra tồn tại tên đăng nhập, email hoặc số điện thoại
    $username_query = "SELECT * FROM users WHERE username = ?";
    $email_query = "SELECT * FROM users WHERE email = ?";
    $stmt_username = $conn->prepare($username_query);
    $stmt_username->bind_param("s", $username);
    $stmt_username->execute();
    $username_result = $stmt_username->get_result();

    $stmt_email = $conn->prepare($email_query);
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $email_result = $stmt_email->get_result();

    if ($username_result->num_rows > 0 || $email_result->num_rows > 0) {
        $errors['username_email'] = 'Tên đăng nhập hoặc email đã tồn tại!';
    }

    // Nếu không có lỗi và người dùng nhấn "Gửi OTP"
    if (empty($errors) && isset($_POST['send_otp'])) {
        $sent_otp = rand(1000, 9999);
        $_SESSION['otp'] = md5($sent_otp);
        send_otp_email($email, $sent_otp); 
        $otp_success_message = "Mã OTP đã được gửi đến email của bạn!";
    }
    
    // Kiểm tra mã OTP khi người dùng đã nhập OTP và nhấn nút đăng ký
    if (isset($_POST['submit'])) {
        $otp = $_POST['otp'] ?? '';
        if (md5($otp) == $_SESSION['otp']) {
            // OTP chính xác, tiếp tục đăng ký
            if (empty($errors)) {
                $hashedUsername = md5($username);        
                $hashedPassword = md5($password);
                $sql = "INSERT INTO users (username, password, name, email, roles) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql);
                $stmt_insert->bind_param("sssss", $hashedUsername, $hashedPassword, $name, $email, $role);
                if ($stmt_insert->execute()) {
                    header("Location: login.php");
                    exit(); 
                } else {
                    $errors['database'] = 'Đăng ký không thành công!';
                }
                $stmt_insert->close();
            }
        } else {
            $errors['otp'] = 'Mã OTP không chính xác!';
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="../img/logo.png" rel="icon">
    <title>Đăng Ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="../css/css-login-register.css">
</head>
<body>
    <div class="site-wrap d-md-flex align-items-stretch">
        <div class="bg-img" style="background-image: url('../img/register-1.png')"></div>
        <div class="form-wrap">
            <div class="form-inner">
                <h1 class="title">Đăng Ký</h1>
                <p class="caption mb-4">Tạo tài khoản của bạn chỉ trong vài giây.</p>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- <?php
                    if (isset($otp_success_message)) {
                        echo "<p>$otp_success_message</p>";
                    }
                    echo "<p>Mã OTP Mã hóa: " . $_SESSION['otp'] . "</p>";
                    echo "<p>Mã OTP gốc: $sent_otp</p>";
                    ?> -->
                
                <?php if (isset($otp_success_message)): ?>
                <div class="alert alert-success" id="otpSuccessMessage">
                <?php echo $otp_success_message; ?>
                </div>
                    <script>
                        setTimeout(function() {
                            document.getElementById('otpSuccessMessage').style.display = 'none';
                        }, 5000);
                    </script>
                <?php endif; ?>
                <form action="" method="POST" class="pt-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Tên Đăng Nhập" value="<?php echo htmlspecialchars($username); ?>" required>
                        <label for="username">Tên Đăng Nhập</label>
                    </div>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="name" id="name" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($name); ?>" required>
                        <label for="name">Họ và Tên</label>
                    </div>
                    <div class="form-floating">
                        <input type="email" class="form-control" name="email" id="email" placeholder="info@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="email">Địa Chỉ Email</label>
                    </div>
                    <div class="form-floating">
                        <span class="password-show-toggle js-password-show-toggle"><span class="uil"></span></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Mật khẩu" value="<?php echo htmlspecialchars($password); ?>" required>
                        <label for="password">Mật Khẩu</label>
                    </div>
                    <div class="form-floating">
                        <span class="password-show-toggle js-password-show-toggle"><span class="uil"></span></span>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Xác Nhận Mật Khẩu" value="<?php echo htmlspecialchars($confirm_password); ?>" required>
                        <label for="confirm_password">Xác Nhận Mật Khẩu</label>
                    </div>

                    <div class="form-floating d-flex mb-4">

                    <button type="submit" class="btn btn-secondary" name="send_otp" id="send_otp" style="width: 150px;">Gửi OTP</button>
                    
                    <!-- Trường Nhập Mã OTP (chỉ hiển thị khi đã gửi OTP) -->
                    <?php if (!empty($sent_otp)): ?>
                        <input type="text" class="form-control ms-3" name="otp" id="otp" placeholder="Mã OTP" required style="width: 200px;">
                    <?php endif; ?>
                </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary" name="submit">Đăng Ký</button>
                    </div>
                    <div class="mb-2">Đã có tài khoản? <a href="login.php">Đăng Nhập</a></div>
                    <div class="social-account-wrap">
                        <h4 class="mb-4"><span>hoặc tiếp tục với</span></h4>
                        <ul class="list-unstyled social-account d-flex justify-content-between">
                            <li><a href="#"><img src="../img/Icon/icon-google.svg" alt="Logo Google"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-facebook.svg" alt="Logo Facebook"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-apple.svg" alt="Logo Apple"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-twitter.svg" alt="Logo Twitter"></a></li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <a href="../../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>
</body>
</html>
