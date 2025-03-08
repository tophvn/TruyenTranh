<?php
include('../config/database.php');
session_start();

$slug = $_GET['slug'] ?? '';
$page = $_GET['page'] ?? 1;

// Kiểm tra nếu slug không rỗng
if ($slug) {
    $api_url = "https://otruyenapi.com/v1/api/the-loai/{$slug}?page={$page}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['data']) && !empty($data['data']['items'])) {
        $truyenList = $data['data']['items'];
        $totalPages = $data['data']['total_pages'] ?? 0;
    } else {
        $truyenList = [];
        $totalPages = 0;
    }
} else {
    $truyenList = [];
    $totalPages = 0;
}

// Hàm tính khoảng thời gian từ ngày cập nhật
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

// Hàm lấy lượt xem từ cơ sở dữ liệu
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
    <meta name="description" content="Đọc truyện tranh miễn phí tại TRUYENTRANHNET theo thể loại <?= htmlspecialchars($slug) ?>.">
    <meta property="og:title" content="TRUYENTRANHNET - Thể Loại <?= htmlspecialchars($slug) ?>">
    <meta property="og:description" content="Khám phá truyện tranh theo thể loại <?= htmlspecialchars($slug) ?> tại TRUYENTRANHNET.">
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com/the-loai/<?= htmlspecialchars($slug) ?>">
    <meta name="robots" content="index, follow">
    <link href="../img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Thể Loại <?= htmlspecialchars($slug) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    </style>
</head>
<body class="dark-mode">
    <?php include '../includes/header.php'; ?>
    <div class="container my-4">
    <br><br><br><br><h4 class="section-title text-center">THỂ LOẠI: <?= strtoupper(htmlspecialchars($slug)) ?></h4>
        <div class="row g-4 fade-in">
            <?php if (!empty($truyenList)): ?>
                <?php foreach ($truyenList as $truyen): ?>
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
                                    <i class="fas fa-eye"></i> <?= getViews($truyen['slug']) ?> lượt xem
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
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?slug=<?= htmlspecialchars($slug) ?>&page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>