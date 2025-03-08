<?php
include('../config/database.php');
session_start();

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
    <br><br><br><h4 class="section-title text-center"><i class="fas fa-search"></i> TÌM KIẾM TRUYỆN</h4>
        <form method="GET" action="tim-kiem.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Nhập từ khóa tìm kiếm" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <?php
        if (isset($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
            $apiUrl = "https://otruyenapi.com/v1/api/tim-kiem?keyword=" . urlencode($keyword);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                echo "<p class='text-center'>Không thể kết nối tới Server.</p>";
            } else {
                $result = json_decode($response, true);
                if (!$result || $result['status'] !== 'success' || !isset($result['data']['items'])) {
                    echo "<p class='text-center'>Không có kết quả tìm kiếm cho từ khóa: " . htmlspecialchars($keyword) . "</p>";
                } else {
                    $searchResults = $result['data']['items'];
        ?>
                    <h5 class="mt-4 text-center">KẾT QUẢ TÌM KIẾM CHO: "<?= htmlspecialchars($keyword) ?>"</h5>
                    <div class="row g-4 fade-in">
                        <?php if (!empty($searchResults)): ?>
                            <?php foreach ($searchResults as $comic): ?>
                                <div class="col-6 col-md-4 col-lg-2 mb-4">
                                    <div class="manga-card">
                                        <a href="../views/truyen-detail.php?slug=<?= urlencode($comic['slug']) ?>" class="text-decoration-none position-relative">
                                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($comic['thumb_url']) ?>" 
                                                 class="card-img-top manga-cover" 
                                                 alt="<?= htmlspecialchars($comic['name']) ?>" 
                                                 loading="lazy">
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
                                        </a>
                                        <div class="card-body p-2">
                                            <h5 class="manga-title" title="<?= htmlspecialchars($comic['name']) ?>"><?= htmlspecialchars($comic['name']) ?></h5>
                                            <div class="text-muted small d-flex justify-content-between align-items-center mt-1">
                                                <span><i class="fas fa-bookmark"></i> <?= htmlspecialchars($comic['chaptersLatest'][0]['chapter_name'] ?? 'N/A') ?></span>
                                                <span><i class="fas fa-clock"></i> <?= timeAgo($comic['updatedAt'] ?? null) ?></span>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-eye"></i> <?= getViews($comic['slug']) ?> lượt xem
                                            </div>
                                            <!-- <div class="text-muted small mt-1">
                                                <i class="fas fa-info-circle"></i> <?= translateStatus(htmlspecialchars($comic['status'])) ?>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">Không có dữ liệu để hiển thị.</p>
                        <?php endif; ?>
                    </div>
        <?php
                }
            }
        }
        ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>