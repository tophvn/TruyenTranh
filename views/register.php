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
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include('../includes/header.php'); ?>

    <main class="container mx-auto px-4 py-8 pt-16 flex items-center justify-center min-h-screen">
        <div class="content-wrapper max-w-md w-full bg-gray-800 p-6 rounded-lg shadow-lg">
            <h1 class="text-center text-3xl font-bold mb-4">Đăng Ký</h1>
            <p class="text-center text-gray-400 mb-6">Tạo tài khoản của bạn chỉ trong vài giây.</p>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <?php foreach ($errors as $error): ?>
                        <p class="text-sm"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($otp_success_message)): ?>
                <div class="bg-green-500 text-white p-3 rounded-lg mb-4" id="otpSuccessMessage">
                    <?php echo $otp_success_message; ?>
                </div>
                <script>
                    setTimeout(() => {
                        document.getElementById('otpSuccessMessage').style.display = 'none';
                    }, 5000);
                </script>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium mb-1">Tên Đăng Nhập</label>
                    <input type="text" id="username" name="username" placeholder="Tên Đăng Nhập" value="<?php echo htmlspecialchars($username); ?>" required
                           class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Họ và Tên</label>
                    <input type="text" id="name" name="name" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($name); ?>" required
                           class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Địa Chỉ Email</label>
                    <input type="email" id="email" name="email" placeholder="info@example.com" value="<?php echo htmlspecialchars($email); ?>" required
                           class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Mật khẩu</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Mật khẩu" value="<?php echo htmlspecialchars($password); ?>" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="js-password-show-toggle absolute right-2 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-300"
                              onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium mb-1">Xác Nhận Mật Khẩu</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác Nhận Mật Khẩu" value="<?php echo htmlspecialchars($confirm_password); ?>" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="js-password-show-toggle absolute right-2 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-300"
                              onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="flex space-x-4 mb-4">
                    <button type="submit" name="send_otp" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                        Gửi OTP
                    </button>
                    <?php if (!empty($_SESSION['otp'])): ?>
                        <input type="text" name="otp" id="otp" placeholder="Mã OTP" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php endif; ?>
                </div>
                <button type="submit" name="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    Đăng Ký
                </button>
                <div class="text-center text-sm text-gray-400 mt-4">
                    Đã có tài khoản? <a href="login.php" class="text-blue-400 hover:text-blue-300">Đăng nhập</a>
                </div>
                
            </form>
        </div>
    </main>

    <?php include('../includes/footer.php'); ?>

    <a href="../index.php" class="fixed bottom-6 right-6 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-gray-200 transition duration-300 z-50">
        <i class="uil uil-estate text-blue-600 text-xl"></i>
    </a>

    <script>
        // Hàm toggle hiển thị mật khẩu
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                toggle.querySelector('i').classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                toggle.querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Xử lý menu hamburger (giả định trong header.php)
        const hamburger = document.getElementById('hamburger');
        if (hamburger) {
            hamburger.addEventListener('click', function() {
                const navMenu = document.getElementById('nav-menu');
                if (navMenu) {
                    navMenu.classList.toggle('hidden');
                    if (!navMenu.classList.contains('hidden')) {
                        navMenu.classList.add('animate-slide-down');
                        setTimeout(() => navMenu.classList.remove('animate-slide-down'), 300);
                    }
                }
            });
        }
    </script>
</body>
</html>