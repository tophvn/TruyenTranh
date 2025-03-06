<?php
// Gọi API để lấy danh sách thể loại truyện
$api_url = "https://otruyenapi.com/v1/api/the-loai";

// Sử dụng cURL để gọi API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

// Chuyển đổi dữ liệu JSON thành mảng PHP
$data = json_decode($response, true);

// Kiểm tra dữ liệu trả về có hợp lệ không
if (isset($data['data']) && !empty($data['data'])) {
    $allCategories = $data['data']['items']; // Lấy tất cả thể loại truyện
} else {
    $allCategories = []; // Nếu không có dữ liệu, trả về mảng trống
}

// Thêm logic phân trang thủ công
$page = $_GET['page'] ?? 1; // Lấy số trang từ URL, mặc định là 1
$itemsPerPage = 12; // Số thể loại mỗi trang (có thể điều chỉnh)
$totalItems = count($allCategories); // Tổng số thể loại
$totalPages = ceil($totalItems / $itemsPerPage); // Tính tổng số trang

// Lấy danh sách thể loại cho trang hiện tại
$start = ($page - 1) * $itemsPerPage;
$categories = array_slice($allCategories, $start, $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Danh Sách Thể Loại Truyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <?php include '../includes/header.php' ?>
    <div class="container mt-4">
        <h4 class="section-title">Thể Loại Truyện</h4>
        <div class="row g-3">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="col-6 col-md-4 col-lg-2 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                                <a href="truyen-theo-the-loai.php?slug=<?php echo $category['slug']; ?>" class="btn btn-primary">Xem Truyện</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có dữ liệu thể loại để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- Thanh chuyển trang -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination justify-content-center mt-4">
                <ul class="pagination">
                    <li class="page-item <?= ($page == 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= ($page - 1) ?>">Trước</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                        <li class="page-item">
                            <a class="page-link <?= ($i == $page) ? 'active-page' : '' ?>" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= ($page + 1) ?>">Tiếp</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>