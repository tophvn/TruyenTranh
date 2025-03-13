<?php
session_start();
include('../config/database.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Người dùng không tồn tại.";
    exit();
}

$error = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $avatar = $_FILES['avatar'];

    if ($avatar['name']) {
        // Kiểm tra định dạng tệp
        $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif']; // Các định dạng được phép
        if (!in_array($avatar['type'], $allowedFileTypes)) {
            $error = "Định dạng tệp không hợp lệ. Vui lòng tải lên ảnh JPEG, PNG hoặc GIF.";
        } elseif ($avatar['size'] > 2 * 1024 * 1024) { // 2MB
            $error = "Ảnh đại diện không được vượt quá 2MB.";
        } else {
            // Upload ảnh lên Imgbb API
            $apiKey = '643885b88cdae3183c2ddd0e9ae4b5bc';  // Thay bằng API key của bạn
            $imageData = base64_encode(file_get_contents($avatar['tmp_name']));
            
            $url = 'https://api.imgbb.com/1/upload?key=' . $apiKey;
            $data = [
                'image' => $imageData,
            ];

            $options = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($data),
                ],
            ];

            $context  = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === FALSE) {
                $error = "Có lỗi xảy ra khi tải lên ảnh đại diện.";
            } else {
                $responseData = json_decode($response, true);
                if ($responseData['success']) {
                    $avatar_url = $responseData['data']['url'];  // Lấy URL ảnh từ response
                    // Cập nhật thông tin người dùng
                    $update_query = "UPDATE users SET name = ?, avatar = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ssi", $name, $avatar_url, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Thông tin tài khoản đã được cập nhật thành công.";
                        // Cập nhật avatar trong session
                        $_SESSION['user']['avatar'] = $avatar_url;
                    } else {
                        $error = "Có lỗi xảy ra khi cập nhật thông tin.";
                    }
                } else {
                    $error = "Không thể tải ảnh lên Imgbb.";
                }
            }
        }
    } else {
        // Cập nhật chỉ tên mà không thay đổi ảnh đại diện
        $update_query = "UPDATE users SET name = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $name, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Thông tin tài khoản đã được cập nhật thành công.";
        } else {
            $error = "Có lỗi xảy ra khi cập nhật thông tin.";
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
    <title>Cập nhật Tài Khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16">
        <div class="content-wrapper max-w-2xl mx-auto">
            <h1 class="text-center py-4 bg-gradient-to-r from-blue-500 to-blue-300 text-white rounded-lg shadow-lg mb-5 text-2xl font-bold uppercase">
                <i class="fa fa-user-edit"></i> CẬP NHẬT TÀI KHOẢN
            </h1>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500 text-white p-4 rounded-lg mb-4 flex items-center">
                    <i class="fa fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="bg-green-500 text-white p-4 rounded-lg mb-4 flex items-center">
                    <i class="fa fa-check-circle mr-2"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium mb-1 flex items-center">
                            <i class="fa fa-user mr-2"></i> Họ tên
                        </label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1 flex items-center">
                            <i class="fa fa-envelope mr-2"></i> Email
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled
                               class="w-full p-2 bg-gray-700 text-white rounded-lg cursor-not-allowed">
                    </div>
                    <div>
                        <label for="avatar" class="block text-sm font-medium mb-1 flex items-center">
                            <i class="fa fa-image mr-2"></i> Ảnh đại diện
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" required
                               class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center justify-center">
                            <i class="fa fa-save mr-2"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>