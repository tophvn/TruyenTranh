<?php 
$base_dir = dirname(__DIR__);
$visitFile = $base_dir . '/visit_count.txt';
$randomUsersFile = $base_dir . '/random_users.txt'; 

if (!file_exists($visitFile)) {
    file_put_contents($visitFile, '0');
}
$visitCount = (int) file_get_contents($visitFile);
$visitCount++;
file_put_contents($visitFile, $visitCount);

function getRandomActiveUsers($file, $interval = 40) {
    $currentTime = time();
    $data = ['count' => rand(2, 50), 'timestamp' => $currentTime]; 

    if (file_exists($file)) {
        $storedData = unserialize(file_get_contents($file));
        if (is_array($storedData) && isset($storedData['count']) && isset($storedData['timestamp'])) {
            $data = $storedData;
            if ($currentTime - $data['timestamp'] >= $interval) {
                $data = ['count' => rand(2, 50), 'timestamp' => $currentTime];
                file_put_contents($file, serialize($data));
            }
        }
    } else {
        file_put_contents($file, serialize($data));
    }

    return $data['count'];
}

$activeUserCount = getRandomActiveUsers($randomUsersFile);

// $base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/views';
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/');
?>

<footer class="footer bg-gray-800 text-white py-8 border-t-2 border-blue-600">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Phần logo và thương hiệu -->
            <div class="footer-section bg-gray-700 p-5 rounded-lg shadow-lg border border-gray-600 hover:-translate-y-1 hover:shadow-xl transition">
                <a href="<?= $base_url; ?>" class="footer-logo">
                    <span class="logo-text text-3xl font-bold text-blue-500 uppercase tracking-wide hover:text-blue-400 transition">TRUYENTRANHNET</span>
                </a>
                <p class="footer-slogan text-gray-300 text-sm mt-2">Kho truyện tranh miễn phí cập nhật 24/7</p>
            </div>
            <!-- Phần liên kết -->
            <div class="footer-section bg-gray-700 p-5 rounded-lg shadow-lg border border-gray-600 hover:-translate-y-1 hover:shadow-xl transition">
                <h5 class="section-title text-blue-500 text-lg uppercase tracking-wide mb-3">Liên Kết</h5>
                <ul class="footer-links space-y-2">
                    <li><a href="<?= $base_url; ?>/views/ve-chung-toi.php" class="footer-link text-white hover:text-blue-400 hover:pl-2 transition text-sm">Về Chúng Tôi</a></li>
                    <li><a href="<?= $base_url; ?>/views/chinh-sach-bao-mat.php" class="footer-link text-white hover:text-blue-400 hover:pl-2 transition text-sm">Chính Sách Bảo Mật</a></li>
                    <li><a href="<?= $base_url; ?>/views/lien-he.php" class="footer-link text-white hover:text-blue-400 hover:pl-2 transition text-sm">Liên Hệ</a></li>
                </ul>
            </div>
            <!-- Phần thông tin và lượt truy cập -->
            <div class="footer-section bg-gray-700 p-5 rounded-lg shadow-lg border border-gray-600 hover:-translate-y-1 hover:shadow-xl transition">
                <h5 class="section-title text-blue-500 text-lg uppercase tracking-wide mb-3">Thông Tin</h5>
                <p class="footer-info text-gray-300 text-sm mb-2">© 2024 TRUYENTRANHNET</p>
                <p class="footer-visits text-gray-300 text-sm mb-2"><i class="fas fa-eye mr-1"></i> Lượt truy cập: <span class="visit-count font-semibold text-blue-400"><?= number_format($visitCount); ?></span></p>
                <p class="footer-active-users text-gray-300 text-sm"><i class="fas fa-users mr-1"></i> Người đang truy cập: <span class="active-count font-semibold text-blue-400"><?= number_format($activeUserCount); ?></span></p>
            </div>
        </div>
    </div>
</footer>