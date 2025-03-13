<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/views';
// Xác định base_url động
$base_url = dirname($_SERVER['SCRIPT_NAME'], substr_count($_SERVER['SCRIPT_NAME'], '/') - 1);

$api_url = "https://otruyenapi.com/v1/api/the-loai";

// Gọi API bằng cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

// Xử lý dữ liệu JSON
$data = json_decode($response, true);
$categories = (isset($data['data']) && !empty($data['data']['items'])) 
    ? $data['data']['items'] 
    : [];
?>

<header class="bg-gray-900 fixed w-full top-0 z-50 shadow-lg py-3" x-data="{ menuOpen: false, categoryOpen: false }">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= $base_url ?>/index.php" class="flex items-center space-x-2 group no-underline">
            <i class="fas fa-book-open text-green-500 text-2xl transition-transform group-hover:scale-110 duration-300"></i>
            <span class="text-xl font-bold text-white group-hover:text-green-500 transition-colors duration-300">TRUYENTRANHNET</span>
        </a>

        <!-- Hamburger Button -->
        <button @click="menuOpen = !menuOpen" class="lg:hidden text-2xl text-white hover:text-green-500 transition-colors duration-300">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Desktop Menu -->
        <nav class="hidden lg:flex items-center space-x-6">
            <form method="GET" action="<?= $base_url ?>/views/tim-kiem.php" class="flex">
                <input type="text" name="keyword" placeholder="Tìm kiếm..." required class="px-3 py-1 rounded-l-lg bg-gray-800 border-none text-white focus:ring-2 focus:ring-green-500 w-48 transition-all duration-300">
                <button type="submit" class="bg-green-500 px-3 py-1 rounded-r-lg hover:bg-green-600 text-white transition-all duration-300">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="<?= $base_url ?>/views/truyen-moi.php" class="text-white hover:text-green-500 transition-all duration-300">Truyện Mới</a>
            <a href="<?= $base_url ?>/views/hoan-thanh.php" class="text-white hover:text-green-500 transition-all duration-300">Hoàn Thành</a>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="text-white hover:text-green-500 transition-all duration-300">Thể Loại</button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 bg-gray-800 rounded-lg shadow-xl mt-2 p-3 w-64 max-h-80 overflow-y-auto z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= $base_url ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="block px-2 py-1 hover:bg-gray-700 text-white rounded transition-colors duration-300 no-underline">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="block px-2 py-1 text-gray-400">Không có thể loại</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (isset($_SESSION['user']['user_id'])): ?>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2">
                        <img src="<?= !empty($_SESSION['user']['avatar']) ? htmlspecialchars($_SESSION['user']['avatar']) : 'img/default-avatar.jpg' ?>" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-green-500 transition-transform hover:scale-105 duration-300">
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute bg-gray-800 rounded-lg shadow-xl mt-2 right-0 p-2 w-48 z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <a href="<?= $base_url ?>/views/tai-khoan.php" class="block px-2 py-1 hover:bg-gray-700 text-white rounded transition-colors duration-300 no-underline">Tài khoản</a>
                        <a href="<?= $base_url ?>/views/following.php" class="block px-2 py-1 hover:bg-gray-700 text-white rounded transition-colors duration-300 no-underline">Theo dõi</a>
                        <a href="<?= $base_url ?>/views/lich-su-doc.php" class="block px-2 py-1 hover:bg-gray-700 text-white rounded transition-colors duration-300 no-underline">Lịch sử đọc</a>
                        <a href="<?= $base_url ?>/views/logout.php" class="block px-2 py-1 hover:bg-red-700 text-red-500 hover:text-white rounded transition-colors duration-300 no-underline">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $base_url ?>/views/login.php" class="bg-green-500 px-3 py-1 rounded-lg hover:bg-green-600 text-white transition-all duration-300 no-underline">Đăng nhập</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Mobile Menu -->
    <div x-show="menuOpen" class="lg:hidden bg-gray-800 text-white fixed top-14 left-0 w-full z-50 p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-full" x-transition:enter-end="opacity-100 transform translate-y-0">
        <form method="GET" action="<?= $base_url ?>/views/tim-kiem.php" class="flex mb-4">
            <input type="text" name="keyword" placeholder="Tìm kiếm..." required class="px-3 py-1 rounded-l-lg bg-gray-700 border-none text-white w-full transition-all duration-300">
            <button type="submit" class="bg-green-500 px-3 py-1 rounded-r-lg hover:bg-green-600 text-white transition-all duration-300">
                <i class="fas fa-search"></i>
            </button>
        </form>
        <a href="<?= $base_url ?>/views/truyen-moi.php" class="block py-2 text-white hover:text-green-500 transition-all duration-300 no-underline">Truyện Mới</a>
        <a href="<?= $base_url ?>/views/hoan-thanh.php" class="block py-2 text-white hover:text-green-500 transition-all duration-300 no-underline">Hoàn Thành</a>
        <div x-data="{ open: false }">
            <button @click="open = !open" class="block py-2 text-white hover:text-green-500 transition-all duration-300 w-full text-left">Thể Loại</button>
            <div x-show="open" class="pl-4 space-y-2 max-h-60 overflow-y-auto">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <a href="<?= $base_url ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="block py-1 text-white hover:text-green-500 transition-all duration-300 no-underline">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="block py-1 text-gray-400">Không có thể loại</span>
                <?php endif; ?>
            </div>
        </div>
        <?php if (isset($_SESSION['user']['user_id'])): ?>
            <a href="<?= $base_url ?>/views/tai-khoan.php" class="block py-2 text-white hover:text-green-500 transition-all duration-300 no-underline">Tài khoản</a>
            <a href="<?= $base_url ?>/views/following.php" class="block py-2 text-white hover:text-green-500 transition-all duration-300 no-underline">Theo dõi</a>
            <a href="<?= $base_url ?>/views/lich-su-doc.php" class="block py-2 text-white hover:text-green-500 transition-all duration-300 no-underline">Lịch sử đọc</a>
            <a href="<?= $base_url ?>/views/logout.php" class="block px-2 py-1 hover:bg-red-700 text-red-500 hover:text-white rounded transition-colors duration-300 no-underline">Đăng xuất</a>
        <?php else: ?>
            <a href="<?= $base_url ?>/views/login.php" class="block bg-green-500 px-4 py-2 rounded-lg hover:bg-green-600 text-white transition-all duration-300 no-underline mt-2">Đăng nhập</a>
        <?php endif; ?>
    </div>
</header>

<!-- Thêm script cần thiết trong index.php, không để trong header.php -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

