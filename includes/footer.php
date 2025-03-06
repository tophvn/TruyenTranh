<?php 
$base_url = dirname($_SERVER['SCRIPT_NAME'], substr_count($_SERVER['SCRIPT_NAME'], '/') - 1);
// Đường dẫn đến file lưu số lượt truy cập
$visitFile = 'visit_count.txt';
// Kiểm tra nếu file chưa tồn tại thì tạo mới và khởi tạo = 0
if (!file_exists($visitFile)) {
    file_put_contents($visitFile, '0');
}
// Đọc số lượt truy cập hiện tại
$visitCount = (int) file_get_contents($visitFile);
$visitCount++;
file_put_contents($visitFile, $visitCount);
?>

<footer class="bg-dark text-white py-3">
    <div class="container">
        <nav class="d-flex justify-content-center mb-3">
            <a href="<?= $base_url; ?>/views/ve-chung-toi.php" class="text-white mx-2">Về Chúng Tôi</a> |
            <a href="<?= $base_url; ?>/views/chinh-sach-bao-mat.php" class="text-white mx-2">Chính Sách Bảo Mật</a> |
            <a href="<?= $base_url; ?>/views/lien-he.php" class="text-white mx-2">Liên Hệ</a>
        </nav>
        <p class="text-center mb-0">
            © 2024 TRUYENTRANHNET. Đọc truyện tranh miễn phí cập nhật nhanh nhất.
            <br>Số lượt truy cập: <?= number_format($visitCount); ?>
        </p>
    </div>
</footer>