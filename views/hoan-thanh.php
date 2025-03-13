<?php
include('../config/database.php');
$type = 'hoan-thanh';
$page = max(1, (int)($_GET['page'] ?? 1)); // Đảm bảo page không nhỏ hơn 1
session_start();

$api_url = "https://otruyenapi.com/v1/api/danh-sach/{$type}?page={$page}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$truyenList = (isset($data['data']) && !empty($data['data']['items'])) ? $data['data']['items'] : [];
$totalItems = $data['data']['params']['pagination']['totalItems'] ?? 0;
$itemsPerPage = $data['data']['params']['pagination']['totalItemsPerPage'] ?? 24;
$totalPages = ($itemsPerPage > 0) ? ceil($totalItems / $itemsPerPage) : 1;

// Hàm định dạng lượt xem
function formatViews($views) {
    if ($views >= 1000000) {
        return number_format($views / 1000000, 1, '.', '') . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1, '.', '') . 'K';
    }
    return $views;
}

// Hàm tính khoảng thời gian từ ngày cập nhật đến hiện tại
function timeAgo($dateString) {
    if (empty($dateString) || $dateString === null) {
        return 'Chưa cập nhật';
    }
    try {
        $updateTime = new DateTime($dateString);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($updateTime);
        
        if ($interval->y > 0) {
            $text = $interval->y . ' năm trước';
        } elseif ($interval->m > 0) {
            $text = $interval->m . ' tháng trước';
        } elseif ($interval->d > 0) {
            $text = $interval->d . ' ngày trước';
        } elseif ($interval->h > 0) {
            $text = $interval->h . ' giờ trước';
        } elseif ($interval->i > 0) {
            $text = $interval->i . ' phút trước';
        } else {
            $text = 'Vừa xong';
        }
        
        return $text;
    } catch (Exception $e) {
        return 'Chưa cập nhật';
    }
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="truyện tranh, manga, manhwa, manhua, đọc truyện miễn phí, TRUYENTRANHNET">
    <meta name="description" content="Danh sách truyện tranh hoàn thành tại TRUYENTRANHNET. Đọc manga, manhwa, manhua miễn phí.">
    <meta property="og:title" content="TRUYENTRANHNET - Danh Sách Truyện Hoàn Thành">
    <meta property="og:description" content="Danh sách truyện tranh hoàn thành tại TRUYENTRANHNET. Đọc manga, manhwa, manhua miễn phí.">
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com/hoan-thanh">
    <meta name="robots" content="index, follow">
    <link href="../img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Danh Sách Truyện Hoàn Thành</title>
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
        <h4 class="text-2xl font-semibold mb-6 text-center"><i class="fas fa-check-circle"></i> DANH SÁCH TRUYỆN HOÀN THÀNH</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3" x-data="{ visible: false }" x-init="$nextTick(() => visible = true)" :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'" x-transition.duration.500ms>
            <?php if (!empty($truyenList)): ?>
                <?php foreach ($truyenList as $truyen): ?>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <a href="../views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="relative block">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                 class="w-full object-cover transition-transform duration-300 group-hover:scale-105" 
                                 alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                 loading="lazy" 
                                 style="aspect-ratio: 3/4; height: 220px;">
                            <?php 
                            if (isset($truyen['category']) && is_array($truyen['category'])) {
                                foreach ($truyen['category'] as $cat) {
                                    if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                        echo '<span class="badge-18plus">18+</span>';
                                        break;
                                    }
                                }
                            }
                            ?>
                            <span class="time-tag"><?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                        </a>
                        <div class="p-2">
                            <h5 class="text-base font-semibold truncate text-white mb-1 text-center">
                                <a href="../views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="hover:text-green-500 transition-colors duration-200"><?= htmlspecialchars($truyen['name']) ?></a>
                            </h5>
                            <div class="flex justify-between items-center text-base text-gray-300 mb-1">
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews(getViews($truyen['slug'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có dữ liệu truyện để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- Thanh phân trang -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination">
                <!-- Nút Previous -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="hoan-thanh.php?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true"><i class="fas fa-chevron-left"></i> Trước</span>
                    </a>
                </li>

                <!-- Hiển thị trang đầu tiên và dấu ... nếu cần -->
                <?php if ($page > 3): ?>
                    <li class="page-item">
                        <a class="page-link" href="hoan-thanh.php?page=1">1</a>
                    </li>
                    <?php if ($page > 4): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Hiển thị tối đa 5 trang gần trang hiện tại -->
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                if ($endPage - $startPage < 4) {
                    if ($startPage === 1) {
                        $endPage = min($totalPages, $startPage + 4);
                    } else {
                        $startPage = max(1, $endPage - 4);
                    }
                }
                ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="hoan-thanh.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Hiển thị trang cuối cùng và dấu ... nếu cần -->
                <?php if ($page < $totalPages - 2): ?>
                    <?php if ($page < $totalPages - 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="hoan-thanh.php?page=<?= $totalPages ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Nút Next -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="hoan-thanh.php?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">Tiếp <i class="fas fa-chevron-right"></i></span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>