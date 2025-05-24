<?php
include('../config/database.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']['user_id'])) {
    header("Location: login.php");
    exit();
}

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = $_SESSION['user']['user_id'];

// Xóa toàn bộ danh sách theo dõi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_all_following'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa danh sách theo dõi!']);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
        exit;
    }

    $userId = $_SESSION['user']['user_id'];
    $deleteQuery = "DELETE FROM yeuthich WHERE user_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Đã xóa toàn bộ danh sách theo dõi!' : 'Lỗi khi xóa: ' . $conn->error
    ]);
    exit;
}

// Xóa từng truyện riêng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_single_following'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa truyện khỏi danh sách theo dõi!']);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
        exit;
    }

    $userId = $_SESSION['user']['user_id'];
    $truyenId = $_POST['truyen_id'];

    $deleteQuery = "DELETE FROM yeuthich WHERE user_id = ? AND truyen_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $userId, $truyenId);
    $success = $stmt->execute();

    if ($success) {
        error_log("Removed single comic from following: userId = $userId, truyenId = $truyenId");
        echo json_encode(['success' => true, 'message' => 'Đã xóa truyện khỏi danh sách theo dõi!']);
    } else {
        error_log("Failed to remove single comic: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa truyện: ' . $conn->error]);
    }
    exit;
}

// Lấy danh sách truyện đang theo dõi
$query = "
    SELECT t.id, t.name, t.slug, t.thumb_url, t.status, t.updated_at 
    FROM truyen t 
    INNER JOIN yeuthich y ON t.id = y.truyen_id 
    WHERE y.user_id = ?
    ORDER BY t.updated_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$followingComics = $result->fetch_all(MYSQLI_ASSOC);

function formatDate($dateString) {
    if ($dateString) {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
    return 'N/A';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Danh Sách Theo Dõi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .comic-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .comic-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .spinner {
            border: 3px solid #e5e7eb;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            width: calc(100% - 40px);
        }
        .notification {
            background-color: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease-out, fadeOut 0.5s ease-in 3.5s forwards;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
            font-size: 1rem;
        }
        .notification.error {
            background-color: #ef4444;
        }
        .notification.success {
            background-color: #10b981;
        }
        .notification .close-btn {
            cursor: pointer;
            font-size: 18px;
            padding-left: 10px;
        }
        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @media (max-width: 640px) {
            .notification-container {
                bottom: 10px;
                right: 10px;
                max-width: calc(100% - 20px);
            }
            .notification {
                padding: 8px 12px;
                font-size: 0.875rem;
            }
            .notification .close-btn {
                font-size: 16px;
                padding-left: 8px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white transition-colors duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-20">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="fa fa-heart text-red-500 mr-2"></i> Danh Sách Theo Dõi
                </h1>
                <button id="removeAllFollowing" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg flex items-center transition duration-200">
                    <i class="fas fa-trash mr-2"></i> Xóa Tất Cả
                    <span id="removeAllSpinner" class="spinner ml-2"></span>
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php if (empty($followingComics)): ?>
                    <p class="col-span-full text-center text-gray-500 dark:text-gray-400 text-lg">Bạn chưa theo dõi truyện nào.</p>
                <?php else: ?>
                    <?php foreach ($followingComics as $index => $comic): ?>
                        <div class="comic-card bg-white dark:bg-gray-800 p-3 rounded-lg shadow-md flex flex-col fade-in" data-truyen-id="<?php echo $comic['id']; ?>" style="animation-delay: <?php echo $index * 0.1; ?>s">
                            <a href="../views/truyen-tranh/<?php echo htmlspecialchars($comic['slug']); ?>">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comic['thumb_url']); ?>" alt="<?php echo htmlspecialchars($comic['name']); ?>" class="w-full h-40 object-cover rounded-md mb-2">
                            </a>
                            <div class="flex-1">
                                <a href="../views/truyen-tranh/<?php echo htmlspecialchars($comic['slug']); ?>" class="text-base font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition line-clamp-2">
                                    <?php echo htmlspecialchars($comic['name']); ?>
                                </a>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Trạng thái: <?php echo htmlspecialchars($comic['status']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Cập nhật: <?php echo formatDate($comic['updated_at']); ?></p>
                            </div>
                            <button class="remove-single-button bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg mt-2 flex items-center justify-center transition duration-200 text-sm" data-truyen-id="<?php echo $comic['id']; ?>">
                                <i class="fas fa-trash mr-1"></i> Xóa
                                <span class="spinner ml-1"></span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div id="notificationContainer" class="notification-container"></div>
    </main>

    <script>
        $(document).ready(function() {
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';

            function showNotification(message, type = 'success') {
                const notification = $(`
                    <div class="notification ${type}">
                        <span>${message}</span>
                        <span class="close-btn">×</span>
                    </div>
                `);
                $('#notificationContainer').append(notification);

                setTimeout(() => {
                    notification.remove();
                }, 4000);

                notification.find('.close-btn').on('click', function() {
                    notification.remove();
                });
            }

            $('#removeAllFollowing').on('click', function() {
                if (confirm('Bạn có chắc muốn xóa toàn bộ danh sách theo dõi?')) {
                    const spinner = $(this).find('.spinner');
                    spinner.show();
                    $(this).prop('disabled', true);

                    $.post({
                        url: '',
                        data: { remove_all_following: true, csrf_token: csrfToken },
                        success: function(data) {
                            const response = JSON.parse(data);
                            spinner.hide();
                            $(this).prop('disabled', false);
                            showNotification(response.message, response.success ? 'success' : 'error');
                            if (response.success) {
                                setTimeout(() => location.reload(), 500);
                            }
                        }.bind(this),
                        error: function() {
                            spinner.hide();
                            $(this).prop('disabled', false);
                            showNotification('Có lỗi xảy ra khi xóa danh sách theo dõi.', 'error');
                        }.bind(this)
                    });
                }
            });

            $('.remove-single-button').on('click', function() {
                const truyenId = $(this).data('truyen-id');
                const item = $(this).closest('.comic-card');
                const spinner = $(this).find('.spinner');

                if (confirm('Bạn có chắc muốn xóa truyện này khỏi danh sách theo dõi?')) {
                    spinner.show();
                    $(this).prop('disabled', true);

                    $.post({
                        url: '',
                        data: { remove_single_following: true, truyen_id: truyenId, csrf_token: csrfToken },
                        success: function(data) {
                            const response = JSON.parse(data);
                            spinner.hide();
                            $(this).prop('disabled', false);
                            showNotification(response.message, response.success ? 'success' : 'error');
                            if (response.success) {
                                item.css('opacity', '0');
                                setTimeout(() => {
                                    item.remove();
                                    if ($('.comic-card').length === 0) {
                                        $('.grid').html('<p class="col-span-full text-center text-gray-500 dark:text-gray-400 text-lg">Bạn chưa theo dõi truyện nào.</p>');
                                    }
                                }, 300);
                            }
                        }.bind(this),
                        error: function() {
                            spinner.hide();
                            $(this).prop('disabled', false);
                            showNotification('Có lỗi xảy ra khi xóa truyện.', 'error');
                        }.bind(this)
                    });
                }
            });
        });
    </script>
</body>
</html>
