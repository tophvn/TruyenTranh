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
            $apiKey = '31218a21d09a44bf72b6cae6f7294940';  // Thay bằng API key của bạn
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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="../img/logo.png" rel="icon">
    <title>Cập nhật Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .form-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .form-container h1 {
            text-align: center;
            color: #495057;
            margin-bottom: 20px;
        }

        .form-container .btn-primary {
            background-color: #0d6efd;
            border: none;
            transition: all 0.3s ease;
        }

        .form-container .btn-primary:hover {
            background-color: #0b5ed7;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
    <div class="container mt-5">
        <div class="form-container mx-auto" style="max-width: 600px;">
            <h1><i class="fa fa-user-edit"></i> TÀI KHOẢN</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <p><i class="fa fa-exclamation-circle"></i> <?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <p><i class="fa fa-check-circle"></i> <?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label"><i class="fa fa-user"></i> Họ tên</label>
                    <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="avatar" class="form-label"><i class="fa fa-image"></i> Ảnh đại diện</label>
                    <input type="file" class="form-control" name="avatar" id="avatar" accept="image/*" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <br>
    <br>
    <?php include '../includes/footer.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>