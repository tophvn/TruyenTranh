<?php
include('../config/database.php'); 
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Kiểm tra nếu có mã xác nhận từ URL (token)
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error_message = "Mã xác nhận không được cung cấp.";
} else {
    $token = trim($_GET['token']); 
    error_log("Token từ URL: " . $token);

    $token = mysqli_real_escape_string($conn, $token);
    $query = "SELECT * FROM users WHERE reset_token = '$token'";
    $result = $conn->query($query);

    error_log("Truy vấn: " . $query);
    error_log("Số hàng trả về: " . ($result ? $result->num_rows : 'Lỗi'));

    if ($result === false) {
        $error_message = "Lỗi truy vấn cơ sở dữ liệu: " . $conn->error;
        error_log("Lỗi truy vấn: " . $conn->error);
    } elseif ($result->num_rows == 0) {
        $error_message = "Mã xác nhận không hợp lệ hoặc đã hết hạn. (Token: '$token')";
        error_log("Không tìm thấy token trong DB: " . $token);
    } else {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        error_log("Token hợp lệ, email: " . $email);
    }
}

$error = ""; 
$success_message = "";

if (isset($email) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (strlen($new_password) < 6) {
        $error = "Mật khẩu phải lớn hơn 6 ký tự.";
    } elseif (preg_match('/[^\w]/', $new_password)) {
        $error = "Mật khẩu không được chứa ký tự đặc biệt.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } else {
        $hashed_password = md5($new_password);
        $hashed_password = mysqli_real_escape_string($conn, $hashed_password);
        $email = mysqli_real_escape_string($conn, $email);
        $update_query = "UPDATE users SET password = '$hashed_password', reset_token = NULL WHERE email = '$email'";
        
        if ($conn->query($update_query)) {
            $success_message = "Mật khẩu của bạn đã được thay đổi thành công.";
        } else {
            $error = "Có lỗi xảy ra khi cập nhật mật khẩu: " . $conn->error;
        }
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
    <title>Đặt Lại Mật Khẩu - TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include('../includes/header.php'); ?>

    <main class="container mx-auto px-4 py-8 pt-16 flex items-center justify-center min-h-screen">
        <div class="content-wrapper max-w-md w-full bg-gray-800 p-6 rounded-lg shadow-lg">
            <h1 class="text-center text-3xl font-bold mb-4">Đặt Lại Mật Khẩu</h1>
            <p class="text-center text-gray-400 mb-6">Nhập mật khẩu mới cho tài khoản của bạn.</p>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <p class="text-sm"><?php echo $error_message; ?></p>
                </div>
                <div class="text-center text-sm text-gray-400 mt-4">
                    Quay lại <a href="login.php" class="text-blue-400 hover:text-blue-300">Đăng Nhập</a>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <p class="text-sm"><?php echo $error; ?></p>
                </div>
            <?php elseif (!empty($success_message)): ?>
                <div class="bg-green-500 text-white p-3 rounded-lg mb-4">
                    <p class="text-sm"><?php echo $success_message; ?></p>
                </div>
                <div class="text-center text-sm text-gray-400 mt-4">
                    Quay lại <a href="login.php" class="text-blue-400 hover:text-blue-300">Đăng Nhập</a>
                </div>
            <?php else: ?>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium mb-1">Mật Khẩu Mới</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Mật khẩu mới" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium mb-1">Xác Nhận Mật Khẩu</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        Cập Nhật Mật Khẩu
                    </button>
                    <div class="text-center text-sm text-gray-400 mt-4">
                        Quay lại <a href="login.php" class="text-blue-400 hover:text-blue-300">Đăng Nhập</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php include('../includes/footer.php'); ?>

    <a href="../index.php" class="fixed bottom-6 right-6 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-gray-200 transition duration-300 z-50">
        <i class="uil uil-estate text-blue-600 text-xl"></i>
    </a>

    <script>
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