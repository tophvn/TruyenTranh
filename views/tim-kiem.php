<?php
include('../config/database.php');
session_start();
// Hàm định dạng thời gian
function formatDate($dateString) {
    if (empty($dateString) || $dateString === null) {
        return 'N/A';
    }
    try {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
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
    <link href="../img/logo.png" rel="icon">
    <title>Tìm Kiếm Truyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css"> <!-- Sử dụng main.css -->
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-4">
        <div class="section-wrapper">
            <h4 class="section-title"><i class="fas fa-search"></i> Tìm Kiếm Truyện</h4>
            <form method="GET" action="tim-kiem.php" class="mb-4">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập từ khóa tìm kiếm" required>
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
                    exit;
                }

                $result = json_decode($response, true);
                if (!$result || $result['status'] !== 'success' || !isset($result['data']['items'])) {
                    echo "<p class='text-center'>Dữ liệu không hợp lệ hoặc không có kết quả tìm kiếm.</p>";
                    exit;
                }

                $searchResults = $result['data']['items'];
            ?>
            <h5 class="mt-4 text-center">Kết Quả Tìm Kiếm Cho: "<?php echo htmlspecialchars($keyword); ?>"</h5>
            <div class="row g-3">
                <?php if (!empty($searchResults)): ?>
                    <?php foreach ($searchResults as $comic): ?>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="manga-card">
                                <a href="truyen-detail.php?slug=<?= urlencode($comic['slug']) ?>" class="text-decoration-none position-relative">
                                    <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($comic['thumb_url']) ?>" 
                                         class="card-img-top manga-cover" 
                                         alt="<?= htmlspecialchars($comic['name']) ?>" 
                                         loading="lazy">
                                    <?php 
                                    // Kiểm tra thể loại để hiển thị tag "18+"
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
                                        <span><i class="fas fa-clock"></i> <?= formatDate($comic['updatedAt'] ?? null) ?></span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-eye"></i> <?= getViews($comic['slug']) ?> lượt xem
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-info-circle"></i> <?= translateStatus(htmlspecialchars($comic['status'])) ?>
                                    </div>
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
            ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

</body>
</html>