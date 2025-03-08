<?php
include('../config/database.php');
$type = 'dang-phat-hanh';
$page = $_GET['page'] ?? 1;
session_start();

$api_url = "https://otruyenapi.com/v1/api/danh-sach/$type?page=$page";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$truyenList = (isset($data['data']) && !empty($data['data'])) ? $data['data']['items'] : [];

// Hàm tính khoảng thời gian từ ngày cập nhật đến hiện tại với cắt ngắn nếu cần
function timeAgo($dateString) {
    if (empty($dateString) || $dateString === null) {
        return 'N/A';
    }
    try {
        $updateTime = new DateTime($dateString);
        $currentTime = new DateTime();
        $interval = $currentTime->diff($updateTime);
        
        if ($interval->y > 0) {
            $text = $interval->y . ' năm trước';
        } elseif ($interval->m > 0) {
            $text = $interval->m . ' tháng trước';
            // Kiểm tra độ dài chuỗi, nếu quá 10 ký tự thì cắt ngắn
            if (strlen($text) > 10) {
                $text = $interval->m . ' tháng trư...'; // Giữ "tháng trư..." thay vì cắt giữa từ
            }
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
        return 'N/A';
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
    <meta name="description" content="Danh sách truyện tranh đang phát hành tại TRUYENTRANHNET. Đọc manga, manhwa, manhua miễn phí.">
    <meta property="og:title" content="TRUYENTRANHNET - Danh Sách Truyện Đang Phát Hành">
    <meta property="og:description" content="Danh sách truyện tranh đang phát hành tại TRUYENTRANHNET. Đọc manga, manhwa, manhua miễn phí.">
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com/dang-phat-hanh">
    <meta name="robots" content="index, follow">
    <link href="../img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Danh Sách Truyện Đang Phát Hành</title>
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
    <br><br><br><br><h4 class="section-title text-center"><i class="fas fa-play"></i> DANH SÁCH TRUYỆN ĐANG PHÁT HÀNH</h4>
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
                <p class="text-center">Không có dữ liệu truyện để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- Thanh chuyển trang -->
        <div class="pagination justify-content-center mt-4">
            <ul class="pagination">
                <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= ($page - 1) ?>">Trước</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $data['data']['totalPages'] ?? $page + 2); $i++): ?>
                    <li class="page-item">
                        <a class="page-link <?= ($i == $page) ? 'active-page' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= ($data['data']['totalPages'] ?? $page)) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= ($page + 1) ?>">Tiếp</a>
                </li>
            </ul>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>