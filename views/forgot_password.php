<?php
include('../config/database.php');
include('../config/send_email.php');
$errors = [];
$message = '';

// Kiểm tra nếu biểu mẫu đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $query = "SELECT user_id FROM users WHERE email = '$email'";
    $result = $conn->query($query); 

    if ($result && $result->num_rows > 0) {
        // Tạo mã đặt lại mật khẩu
        $token = bin2hex(random_bytes(50));
        $update_query = "UPDATE users SET reset_token = '$token' WHERE email = '$email'";
        if ($conn->query($update_query) === TRUE) { 
            send_password_reset_email($email, $token);
            $message = 'Thành công! Truy cập Email của bạn để đổi mật khẩu!';
        } else {
            $errors[] = 'Lỗi khi cập nhật token đặt lại mật khẩu.';
        }
    } else {
        $errors[] = 'Email không tồn tại trong hệ thống.';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Quên Mật Khẩu - TRUYENTRANHNET</title>
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
    <?php include('../includes/header.php'); ?>
    <div class="site-wrap d-md-flex align-items-stretch min-h-screen">
        <div class="bg-img" style="background-image: url('../img/forgot-1.jpg')"></div>
        <div class="form-wrap">
            <div class="form-inner p-4 sm:p-6 md:p-8">
                <h1 class="title text-3xl sm:text-4xl md:text-5xl mb-4">Quên Mật Khẩu</h1>
                <p class="caption mb-4 text-sm sm:text-base">Vui lòng nhập địa chỉ email của bạn để đặt lại mật khẩu.</p>
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
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                        <label for="email">Địa chỉ Email</label>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary">Đặt Lại Mật Khẩu</button>
                    </div>
                    <div class="mb-2 text-center">Quay lại <a href="login.php">Đăng Nhập</a></div>
                </form>
            </div>
        </div>
    </div>
    <a href="../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('hamburger').addEventListener('click', function() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('hidden');
            if (!navMenu.classList.contains('hidden')) {
                navMenu.classList.add('animate-slide-down');
                setTimeout(() => navMenu.classList.remove('animate-slide-down'), 300);
            }
        });
    </script>
</body>
</html>