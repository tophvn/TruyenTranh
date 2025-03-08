<?php
include('../config/database.php'); 
include('../config/send_email.php'); 
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

    // Kiểm tra tồn tại tên đăng nhập, email
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
    
    // Kiểm tra mã OTP khi người dùng nhấn nút đăng ký
    if (isset($_POST['submit'])) {
        $otp = $_POST['otp'] ?? '';
        if (md5($otp) == $_SESSION['otp']) {
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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Đăng Ký - TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/css-login-register.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#2a2e8a',
                        'button-primary': '#4CAF50',
                        'button-hover': '#45a049',
                        'accent': '#ffffff',
                        'hover-bg': '#e0e7ff',
                        'button-glow': '#80e27e',
                    },
                    animation: {
                        'slide-down': 'slide-down 0.3s ease-out',
                        'fade-in': 'fade-in 0.2s ease-in-out',
                        'pulse-glow': 'pulse-glow 2s infinite ease-in-out',
                    },
                    keyframes: {
                        'slide-down': {
                            '0%': { transform: 'translateY(-100%)', opacity: 0 },
                            '100%': { transform: 'translateY(0)', opacity: 1 },
                        },
                        'fade-in': {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 5px rgba(128, 226, 126, 0.3)' },
                            '50%': { boxShadow: "0 0 15px rgba(128, 226, 126, 0.7)" },
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-['Inter'] pt-16 lg:pt-20">
    <?php include('../includes/header.php'); // Thêm header.php ?>

    <!-- Nội dung trang đăng ký -->
    <div class="site-wrap d-md-flex align-items-stretch min-h-screen">
        <div class="bg-img" style="background-image: url('../img/register-1.png')"></div>
        <div class="form-wrap">
            <div class="form-inner p-4 sm:p-6 md:p-8">
                <h1 class="title text-3xl sm:text-4xl md:text-5xl mb-4">Đăng Ký</h1>
                <p class="caption mb-4 text-sm sm:text-base">Tạo tài khoản của bạn chỉ trong vài giây.</p>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Tên Đăng Nhập" value="<?php echo htmlspecialchars($username); ?>" required>
                        <label for="username">Tên Đăng Nhập</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="name" id="name" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($name); ?>" required>
                        <label for="name">Họ và Tên</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" id="email" placeholder="info@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="email">Địa Chỉ Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <span class="password-show-toggle js-password-show-toggle"><span class="uil"></span></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Mật khẩu" value="<?php echo htmlspecialchars($password); ?>" required>
                        <label for="password">Mật Khẩu</label>
                    </div>
                    <div class="form-floating mb-3">
                        <span class="password-show-toggle js-password-show-toggle"><span class="uil"></span></span>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Xác Nhận Mật Khẩu" value="<?php echo htmlspecialchars($confirm_password); ?>" required>
                        <label for="confirm_password">Xác Nhận Mật Khẩu</label>
                    </div>
                    <div class="form-floating d-flex mb-4">
                        <button type="submit" class="btn btn-secondary me-3" name="send_otp" id="send_otp" style="width: 150px;">Gửi OTP</button>
                        <?php if (!empty($sent_otp)): ?>
                            <input type="text" class="form-control" name="otp" id="otp" placeholder="Mã OTP" required style="width: 200px;">
                        <?php endif; ?>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary" name="submit">Đăng Ký</button>
                    </div>
                    <div class="mb-2 text-center">Đã có tài khoản? <a href="login.php">Đăng Nhập</a></div>
                    <div class="social-account-wrap">
                        <h4 class="mb-4"><span>hoặc tiếp tục với</span></h4>
                        <ul class="list-unstyled social-account d-flex justify-content-between">
                            <li><a href="<?php echo $googleLoginUrl ?? '#'; ?>"><img src="../img/Icon/icon-google.svg" alt="Google"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-facebook.svg" alt="Facebook"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-apple.svg" alt="Apple"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-twitter.svg" alt="Twitter"></a></li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Nút quay lại -->
    <a href="../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý hamburger menu
        document.getElementById('hamburger').addEventListener('click', function() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('hidden');
            if (!navMenu.classList.contains('hidden')) {
                navMenu.classList.add('animate-slide-down');
                setTimeout(() => navMenu.classList.remove('animate-slide-down'), 300);
            }
        });

        // Xử lý toggle hiển thị mật khẩu
        document.querySelectorAll('.js-password-show-toggle').forEach(item => {
            item.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                this.classList.toggle('active');
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
</body>
</html>