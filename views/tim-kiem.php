<?php
include('../config/database.php');
session_start();

// Lấy tham số từ URL
$keyword = $_GET['keyword'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1)); // Đảm bảo page không nhỏ hơn 1

// Hàm tính khoảng thời gian từ ngày cập nhật
function timeAgo($dateString) {
    if (empty($dateString)) return 'Chưa cập nhật';
    try {
        $updateTime = new DateTime($dateString);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($updateTime);
        if ($interval->y > 0) return $interval->y . ' năm trước';
        elseif ($interval->m > 0) return $interval->m . ' tháng trước';
        elseif ($interval->d > 0) return $interval->d . ' ngày trước';
        elseif ($interval->h > 0) return $interval->h . ' giờ trước';
        elseif ($interval->i > 0) return $interval->i . ' phút trước';
        else return 'Vừa xong';
    } catch (Exception $e) {
        return 'Chưa cập nhật';
    }
}

// Hàm định dạng lượt xem
function formatViews($views) {
    if ($views >= 1000000) {
        return number_format($views / 1000000, 1, '.', '') . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1, '.', '') . 'K';
    }
    return $views;
}

// Lấy thông tin lượt xem từ cơ sở dữ liệu
function getViews($slug) {
    global $conn;
    $query = "SELECT views FROM truyen WHERE slug = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['views'] ?? 0;
}

// Hàm chuyển đổi trạng thái
function translateStatus($status) {
    switch ($status) {
        case 'ongoing':
            return 'Đang Phát Hành';
        case 'completed':
            return 'Hoàn Thành';
        case 'coming_soon':
            return 'Sắp Ra Mắt';
        default:
            return 'Không Xác Định';
    }
}

// Gọi API tìm kiếm với phân trang
$searchResults = [];
$totalPages = 1;
$totalItems = 0; 
if (!empty($keyword)) {
    $apiUrl = "https://otruyenapi.com/v1/api/tim-kiem?keyword=" . urlencode($keyword) . "&page={$page}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $result = json_decode($response, true);
        if ($result && $result['status'] === 'success' && isset($result['data']['items'])) {
            $searchResults = $result['data']['items'];
            $totalItems = $result['data']['params']['pagination']['totalItems'] ?? 0; 
            $itemsPerPage = $result['data']['params']['pagination']['totalItemsPerPage'] ?? 24;
            $totalPages = ($itemsPerPage > 0) ? ceil($totalItems / $itemsPerPage) : 1;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="truyện tranh, manga, manhwa, manhua, tìm kiếm truyện, TRUYENTRANHNET">
    <meta name="description" content="Tìm kiếm truyện tranh miễn phí tại TRUYENTRANHNET. Khám phá manga, manhwa, manhua yêu thích.">
    <meta property="og:title" content="TRUYENTRANHNET - Tìm Kiếm Truyện">
    <meta property="og:description" content="Tìm kiếm và đọc truyện tranh miễn phí tại TRUYENTRANHNET.">
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com/tim-kiem.php">
    <meta name="robots" content="index, follow">
    <link href="../img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Tìm Kiếm Truyện</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .update-tag {
            background-color: #00b7eb;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .badge-18plus {
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: #ff0000;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            z-index: 10;
        }
        .time-tag {
            position: absolute;
            top: 8px;
            left: 8px;
            background-color: #0099FF;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            z-index: 10;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .pagination .page-item {
            display: inline-block;
        }
        .pagination .page-link {
            background-color: #1f2937;
            color: #00b7eb;
            border: 1px solid #374151;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        .pagination .page-link:hover {
            background-color: #374151;
            color: #fff;
        }
        .pagination .page-item.active .page-link {
            background-color: #00b7eb;
            border-color: #00b7eb;
            color: #fff;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #1f2937;
            color: #6b7280;
            border-color: #374151;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-900 text-white font-poppins">
    <?php include '../includes/header.php'; ?>
    <div class="container mx-auto px-4 py-8 pt-16">
        <h4 class="text-2xl font-semibold mb-6 text-center"><i class="fas fa-search"></i> TÌM KIẾM TRUYỆN</h4>
        <form method="GET" action="tim-kiem.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Nhập từ khóa tìm kiếm" value="<?= htmlspecialchars($keyword) ?>" required>
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <?php if (!empty($keyword)): ?>
            <?php if ($response === false): ?>
                <p class="text-center">Không thể kết nối tới Server.</p>
            <?php elseif (empty($searchResults)): ?>
                <p class="text-center">Không có kết quả tìm kiếm cho từ khóa: "<?= htmlspecialchars($keyword) ?>"</p>
            <?php else: ?>
                <h5 class="mt-6 mb-4 text-xl font-semibold text-center text-blue-400 bg-gray-800 py-3 px-4 rounded-lg shadow-md">
                    KẾT QUẢ TÌM KIẾM CHO: "<?= htmlspecialchars($keyword) ?>" 
                    <span class="text-yellow-300">(<?= number_format($totalItems) ?> Truyện)</span>
                </h5>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3" x-data="{ visible: false }" x-init="$nextTick(() => visible = true)" :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'" x-transition.duration.500ms>
                    <?php foreach ($searchResults as $comic): ?>
                        <div class="bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                            <a href="../views/truyen-tranh/<?= urlencode($comic['slug']) ?>" class="relative block">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($comic['thumb_url']) ?>" 
                                     class="w-full object-cover transition-transform duration-300 group-hover:scale-105" 
                                     alt="<?= htmlspecialchars($comic['name']) ?>" 
                                     loading="lazy" 
                                     style="aspect-ratio: 3/4; height: 220px;">
                                <?php 
                                if (isset($comic['category']) && is_array($comic['category'])) {
                                    foreach ($comic['category'] as $cat) {
                                        if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                            echo '<span class="badge-18plus">18+</span>';
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <span class="time-tag"><?= timeAgo($comic['updatedAt'] ?? null) ?></span>
                            </a>
                            <div class="p-2">
                                <h5 class="text-base font-semibold truncate text-white mb-1 text-center">
                                    <a href="../views/truyen-tranh/<?= urlencode($comic['slug']) ?>" class="hover:text-green-500 transition-colors duration-200"><?= htmlspecialchars($comic['name']) ?></a>
                                </h5>
                                <div class="flex justify-between items-center text-base text-gray-300 mb-1">
                                    <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($comic['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có chương') ?></span>
                                    <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews(getViews($comic['slug'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Thanh phân trang -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="tim-kiem.php?keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true"><i class="fas fa-chevron-left"></i> Trước</span>
                            </a>
                        </li>
                        <?php if ($page > 3): ?>
                            <li class="page-item">
                                <a class="page-link" href="tim-kiem.php?keyword=<?= urlencode($keyword) ?>&page=1">1</a>
                            </li>
                            <?php if ($page > 4): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="tim-kiem.php?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages - 2): ?>
                            <?php if ($page < $totalPages - 3): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="tim-kiem.php?keyword=<?= urlencode($keyword) ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="tim-kiem.php?keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">Tiếp <i class="fas fa-chevron-right"></i></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>
