<?php
include('../config/database.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Kiểm tra session
// if (isset($_SESSION['user']['user_id'])) {
//     error_log("User ID in session (following.php): " . $_SESSION['user']['user_id']);
// } else {
//     error_log("No user ID in session (following.php), redirecting to login");
//     header("Location: login.php");
//     exit;
// }

$userId = $_SESSION['user']['user_id'];

// Xóa toàn bộ danh sách theo dõi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_all_following'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa danh sách theo dõi!']);
        exit;
    }
    
    $userId = $_SESSION['user']['user_id'];
    $deleteQuery = "DELETE FROM yeuthich WHERE user_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Đã xóa toàn bộ danh sách theo dõi!' : 'Lỗi khi xóa'
    ]);
    exit;
}

// Xóa từng truyện riêng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_single_following'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa truyện khỏi danh sách theo dõi!']);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .following-list {
            margin-top: 20px;
        }
        .following-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .following-item img {
            width: 80px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .following-details {
            flex-grow: 1;
        }
        .following-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
        }
        .following-title:hover {
            color: #ff5722;
        }
        .following-status {
            font-size: 14px;
            color: #666;
        }
        .following-updated {
            font-size: 12px;
            color: #999;
        }
        .remove-all-button {
            background-color: #dc3545;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .remove-all-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .remove-single-button {
            background-color: #dc3545;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            font-size: 14px;
            margin-left: 10px;
        }
        .remove-single-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php' ?>

    <main class="container">
        <h1 class="my-4">Danh Sách Theo Dõi</h1>
        <button class="remove-all-button" id="removeAllFollowing">
            <i class="fas fa-trash"></i> Xóa tất cả
        </button>
        <div class="following-list">
            <?php if (empty($followingComics)): ?>
                <p>Bạn chưa theo dõi truyện nào.</p>
            <?php else: ?>
                <?php foreach ($followingComics as $comic): ?>
                    <div class="following-item" data-truyen-id="<?php echo $comic['id']; ?>">
                        <a href="truyen-detail.php?slug=<?php echo htmlspecialchars($comic['slug']); ?>">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comic['thumb_url']); ?>" alt="<?php echo htmlspecialchars($comic['name']); ?>">
                        </a>
                        <div class="following-details">
                            <a href="truyen-detail.php?slug=<?php echo htmlspecialchars($comic['slug']); ?>" class="following-title">
                                <?php echo htmlspecialchars($comic['name']); ?>
                            </a>
                            <p class="following-status">Trạng thái: <?php echo htmlspecialchars($comic['status']); ?></p>
                            <p class="following-updated">Cập nhật: <?php echo formatDate($comic['updated_at']); ?></p>
                        </div>
                        <button class="remove-single-button" title="Xóa truyện này" data-truyen-id="<?php echo $comic['id']; ?>">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php' ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Xóa toàn bộ danh sách theo dõi
            $('#removeAllFollowing').on('click', function() {
                if (confirm('Bạn có chắc muốn xóa toàn bộ danh sách theo dõi?')) {
                    $.post('', { remove_all_following: true }, function(data) {
                        const response = JSON.parse(data);
                        alert(response.message);
                        if (response.success) {
                            location.reload();
                        }
                    }).fail(function() {
                        alert('Có lỗi xảy ra khi xóa danh sách theo dõi.');
                    });
                }
            });

            // Xóa từng truyện riêng
            $('.remove-single-button').on('click', function() {
                const truyenId = $(this).data('truyen-id');
                const item = $(this).closest('.following-item');

                if (confirm('Bạn có chắc muốn xóa truyện này khỏi danh sách theo dõi?')) {
                    $.post('', { remove_single_following: true, truyen_id: truyenId }, function(data) {
                        const response = JSON.parse(data);
                        alert(response.message);
                        if (response.success) {
                            item.remove(); // Xóa phần tử khỏi giao diện
                            if ($('.following-item').length === 0) {
                                $('.following-list').html('<p>Bạn chưa theo dõi truyện nào.</p>');
                            }
                        }
                    }).fail(function() {
                        alert('Có lỗi xảy ra khi xóa truyện.');
                    });
                }
            });
        });
    </script>
</body>
</html>