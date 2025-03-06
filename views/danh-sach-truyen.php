<?php
$slug = $_GET['slug'] ?? ''; // Lấy slug thể loại từ URL

if ($slug) {
    $api_url = "https://otruyenapi.com/v1/api/danh-sach-truyen?category=$slug"; // API giả sử để lấy truyện theo thể loại

    // Sử dụng cURL để gọi API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    // Chuyển đổi dữ liệu JSON thành mảng PHP
    $data = json_decode($response, true);

    if (isset($data['data']) && !empty($data['data'])) {
        $truyenList = $data['data']['items']; // Lấy danh sách truyện theo thể loại
    } else {
        $truyenList = [];
    }
} else {
    $truyenList = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Danh Sách Truyện Theo Thể Loại</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="/">MANGA VN</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link text-white" href="/">Trang chủ</a>
                <a class="nav-link text-white" href="truyen-moi.php">Truyện mới cập nhật</a>
                <a class="nav-link text-white" href="sap-ra-mat.php">Sắp ra mắt</a>
                <a class="nav-link text-white" href="dang-phat-hanh.php">Đang phát hành</a>
                <a class="nav-link text-white" href="hoan-thanh.php">Hoàn thành</a>
                <a class="nav-link text-white" href="#">Liên hệ</a>
            </div>
        </div>
    </nav>

    <!-- Manga List Section -->
    <div class="container mt-4">
        <h4 class="section-title">Truyện Thể Loại: <?php echo htmlspecialchars($slug); ?></h4>
        <div class="manga-grid row">
            <?php if (!empty($truyenList)): ?>
                <?php foreach ($truyenList as $truyen): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?php echo $truyen['thumb_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($truyen['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($truyen['name']); ?></h5>
                                <a href="https://otruyenapi.com/<?php echo $truyen['slug']; ?>" class="btn btn-primary">Xem truyện</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có truyện thuộc thể loại này.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
