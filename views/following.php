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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16">
        <div class="content-wrapper max-w-4xl mx-auto">
            <h1 class="text-center py-4 bg-gradient-to-r from-blue-500 to-blue-300 text-white rounded-lg shadow-lg mb-5 text-2xl font-bold uppercase">
                <i class="fa fa-heart"></i> DANH SÁCH THEO DÕI
            </h1>

            <button id="removeAllFollowing" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 flex items-center justify-center mb-4">
                <i class="fas fa-trash mr-2"></i> Xóa tất cả
            </button>

            <div class="following-list space-y-4">
                <?php if (empty($followingComics)): ?>
                    <p class="text-center text-gray-400">Bạn chưa theo dõi truyện nào.</p>
                <?php else: ?>
                    <?php foreach ($followingComics as $comic): ?>
                        <div class="following-item bg-gray-800 p-4 rounded-lg shadow-md flex items-center space-x-4" data-truyen-id="<?php echo $comic['id']; ?>">
                            <a href="../views/truyen-tranh/<?php echo htmlspecialchars($comic['slug']); ?>">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comic['thumb_url']); ?>" alt="<?php echo htmlspecialchars($comic['name']); ?>" class="w-20 h-28 object-cover rounded-lg">
                            </a>
                            <div class="following-details flex-1">
                                <a href="../views/truyen-tranh/<?php echo htmlspecialchars($comic['slug']); ?>" class="following-title text-lg font-semibold text-blue-400 hover:text-blue-300 transition">
                                    <?php echo htmlspecialchars($comic['name']); ?>
                                </a>
                                <p class="following-status text-sm text-gray-400">Trạng thái: <?php echo htmlspecialchars($comic['status']); ?></p>
                                <p class="following-updated text-xs text-gray-500">Cập nhật: <?php echo formatDate($comic['updated_at']); ?></p>
                            </div>
                            <button class="remove-single-button bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition duration-300" title="Xóa truyện này" data-truyen-id="<?php echo $comic['id']; ?>">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Xóa toàn bộ danh sách theo dõi
            const removeAllButton = document.getElementById('removeAllFollowing');
            removeAllButton.addEventListener('click', () => {
                if (confirm('Bạn có chắc muốn xóa toàn bộ danh sách theo dõi?')) {
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'remove_all_following=true'
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(() => {
                        alert('Có lỗi xảy ra khi xóa danh sách theo dõi.');
                    });
                }
            });

            // Xóa từng truyện riêng
            const removeSingleButtons = document.querySelectorAll('.remove-single-button');
            removeSingleButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const truyenId = button.getAttribute('data-truyen-id');
                    const item = button.closest('.following-item');

                    if (confirm('Bạn có chắc muốn xóa truyện này khỏi danh sách theo dõi?')) {
                        fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `remove_single_following=true&truyen_id=${truyenId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                item.remove();
                                if (document.querySelectorAll('.following-item').length === 0) {
                                    document.querySelector('.following-list').innerHTML = '<p class="text-center text-gray-400">Bạn chưa theo dõi truyện nào.</p>';
                                }
                            }
                        })
                        .catch(() => {
                            alert('Có lỗi xảy ra khi xóa truyện.');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>