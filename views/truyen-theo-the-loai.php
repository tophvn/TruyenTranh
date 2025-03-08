<?php
include('../config/database.php');
session_start();

// Lấy slug thể loại từ URL và giữ nguyên
$categorySlug = $_GET['slug'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

// Lấy danh sách thể loại để lấy tên chính thức của thể loại
$genres_api_url = "https://otruyenapi.com/v1/api/the-loai";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $genres_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$genres_response = curl_exec($ch);
curl_close($ch);
$genres_data = json_decode($genres_response, true);
$genres = $genres_data['data']['items'] ?? [];

// Tìm tên thể loại dựa trên slug
$categoryName = '';
foreach ($genres as $genre) {
    if ($genre['slug'] === $categorySlug) {
        $categoryName = $genre['name'];
        break;
    }
}
// Nếu không tìm thấy tên, dùng slug làm mặc định
$categoryName = $categoryName ?: ucfirst(str_replace('-', ' ', $categorySlug));

if ($categorySlug) {
    $api_url = "https://otruyenapi.com/v1/api/the-loai/{$categorySlug}?page={$page}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['data']) && !empty($data['data']['items'])) {
        $truyenList = $data['data']['items'];
        // Lấy thông tin phân trang từ params.pagination
        $totalItems = $data['data']['params']['pagination']['totalItems'] ?? 0;
        $itemsPerPage = $data['data']['params']['pagination']['totalItemsPerPage'] ?? 24;
        $totalPages = ($itemsPerPage > 0) ? ceil($totalItems / $itemsPerPage) : 1;
    } else {
        $truyenList = [];
        $totalPages = 1;
    }
} else {
    $truyenList = [];
    $totalPages = 1;
}

function timeAgo($dateString) {
    if (empty($dateString)) return 'N/A';
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
        return 'N/A';
    }
}

// Hàm lấy tất cả lượt xem trong một truy vấn
function getAllViews($slugs) {
    global $conn;
    if (empty($slugs)) return [];
    $placeholders = implode(',', array_fill(0, count($slugs), '?'));
    $query = "SELECT slug, views FROM truyen WHERE slug IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($slugs)), ...$slugs);
    $stmt->execute();
    $result = $stmt->get_result();
    $views = [];
    while ($row = $result->fetch_assoc()) {
        $views[$row['slug']] = $row['views'] ?? 0;
    }
    return $views;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="truyện tranh, manga, manhwa, manhua, đọc truyện miễn phí, TRUYENTRANHNET">
    <meta name="description" content="Đọc truyện tranh miễn phí tại TRUYENTRANHNET theo thể loại <?= htmlspecialchars($categoryName) ?>.">
    <meta property="og:title" content="TRUYENTRANHNET - Thể Loại <?= htmlspecialchars($categoryName) ?>">
    <meta property="og:description" content="Khám phá truyện tranh theo thể loại <?= htmlspecialchars($categoryName) ?> tại TRUYENTRANHNET.">
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com/the-loai/<?= htmlspecialchars($categorySlug) ?>">
    <meta name="robots" content="index, follow">
    <link href="../img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Thể Loại <?= htmlspecialchars($categoryName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
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
            top: 10px;
            right: 10px;
            background-color: #ff0000;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            z-index: 10;
        }
        .pagination .page-link {
            color: #00b7eb;
        }
        .pagination .page-item.active .page-link {
            background-color: #00b7eb;
            border-color: #00b7eb;
            color: #fff;
        }
    </style>
</head>
<body class="dark-mode">
    <?php include '../includes/header.php'; ?>
    <div class="container my-4">
        <br><br><br><br>
        <h4 class="section-title text-center">THỂ LOẠI: <?= htmlspecialchars($categoryName) ?></h4>
        <div class="row g-4 fade-in">
            <?php if (!empty($truyenList)): ?>
                <?php 
                $slugs = array_column($truyenList, 'slug');
                $views = getAllViews($slugs);
                foreach ($truyenList as $truyen): ?>
                    <div class="col-6 col-md-4 col-lg-2 mb-4">
                        <div class="manga-card">
                            <a href="../views/truyen-detail.php?slug=<?= urlencode($truyen['slug']) ?>" class="text-decoration-none position-relative">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                     class="card-img-top manga-cover" 
                                     alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                     loading="lazy">
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
                            </a>
                            <div class="card-body p-2">
                                <h5 class="manga-title" title="<?= htmlspecialchars($truyen['name']) ?>"><?= htmlspecialchars($truyen['name']) ?></h5>
                                <div class="text-muted small d-flex justify-content-between align-items-center mt-1">
                                    <span><i class="fas fa-bookmark"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'N/A') ?></span>
                                    <span><i class="fas fa-clock"></i> <?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-eye"></i> <?= $views[$truyen['slug']] ?? 0 ?> lượt xem
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có truyện thuộc thể loại này.</p>
            <?php endif; ?>
        </div>

        <!-- Thanh phân trang -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="truyen-theo-the-loai.php?slug=<?= htmlspecialchars($categorySlug) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">« Trước</span>
                    </a>
                </li>
                <?php if ($page > 3): ?>
                    <li class="page-item">
                        <a class="page-link" href="truyen-theo-the-loai.php?slug=<?= htmlspecialchars($categorySlug) ?>&page=1">1</a>
                    </li>
                    <?php if ($page > 4): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="truyen-theo-the-loai.php?slug=<?= htmlspecialchars($categorySlug) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages - 2): ?>
                    <?php if ($page < $totalPages - 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="truyen-theo-the-loai.php?slug=<?= htmlspecialchars($categorySlug) ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="truyen-theo-the-loai.php?slug=<?= htmlspecialchars($categorySlug) ?>&page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">Tiếp »</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="../js/main.js"></script>
</body>
</html>