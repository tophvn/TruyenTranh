<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/views';

// Xác định base_url động
//$base_url = dirname($_SERVER['SCRIPT_NAME'], substr_count($_SERVER['SCRIPT_NAME'], '/') - 1);

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

// Logic cho carousel
$isIndexPage = basename($_SERVER['PHP_SELF']) === 'index.php';
$storySlugs = ['cau-lac-bo-sieu-cap-ve-nha', 'chainsawman-phan-2', 'ruri-dragon', 'dai-chien-nguoi-khong-lo'];
$stories = [];
foreach ($storySlugs as $slug) {
    $api_url_story = "https://otruyenapi.com/v1/api/truyen-tranh/" . urlencode($slug);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url_story);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['item'])) {
        $stories[$slug] = ['name' => $data['data']['item']['name']];
    } else {
        $stories[$slug] = ['name' => 'Không tìm thấy'];
    }
}

// Hàm hỗ trợ
function getAvatarPath($avatar) {
    return ltrim($avatar, '../');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#2a2e8a',
                        'button-primary': '#4CAF50',
                        'button-hover': '#45a049',
                        'accent': '#ffffff',
                        'hover-bg': '#e0e7ff',
                        'button-glow': '#80e27e',
                    },
                    animation: {
                        'slide-down': 'slide-down 0.3s ease-out',
                        'fade-in': 'fade-in 0.2s ease-in-out',
                        'pulse-glow': 'pulse-glow 2s infinite ease-in-out',
                    },
                    keyframes: {
                        'slide-down': {
                            '0%': { transform: 'translateY(-100%)', opacity: 0 },
                            '100%': { transform: 'translateY(0)', opacity: 1 },
                        },
                        'fade-in': {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 5px rgba(128, 226, 126, 0.3)' },
                            '50%': { boxShadow: "0 0 15px rgba(128, 226, 126, 0.7)" },
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-['Inter'] pt-16 lg:pt-20">
    <!-- Header Chính -->
    <header class="bg-gradient-to-r from-primary via-purple-800 to-teal-700 text-accent fixed w-full top-0 z-50 shadow-lg py-2">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between pt-1">
            <a href="<?= $base_url ?>/index.php" class="flex items-center space-x-2 group no-underline">
                <i class="fas fa-book-open text-button-primary text-2xl transition-transform group-hover:scale-110 duration-300"></i>
                <span class="text-2xl font-bold tracking-wide text-accent group-hover:text-button-primary transition-colors duration-300">TRUYENTRANHNET</span>
            </a>
            <button id="hamburger" class="lg:hidden text-2xl focus:outline-none hover:text-button-primary transition-colors duration-300">
                <i class="fas fa-bars"></i>
            </button>
            <div id="nav-menu" class="hidden lg:flex items-center space-x-6">
                <form method="GET" action="<?= $base_url ?>/views/tim-kiem.php" class="flex">
                    <input type="text" name="keyword" placeholder="Tìm kiếm..." required class="px-3 py-1 rounded-l-lg border-none text-gray-700 focus:ring-2 focus:ring-button-primary w-48 transition-all duration-300">
                    <button type="submit" class="bg-button-primary px-3 py-1 rounded-r-lg hover:bg-button-hover text-white transition-all duration-300 animate-pulse-glow">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="flex items-center space-x-3">
                    <?php if (isset($_SESSION['user']['user_id'])): ?>
                        <div class="relative group">
                            <a href="#" class="flex items-center space-x-2 no-underline">
                                <img src="<?= !empty($_SESSION['user']['avatar']) ? htmlspecialchars($_SESSION['user']['avatar']) : '../img/default-avatar.jpg' ?>" alt="Avatar" class="w-8 h-8 rounded-full border-2 border-button-primary transition-transform hover:scale-105 duration-300">
                            </a>
                            <div class="absolute hidden group-hover:block bg-white text-gray-800 rounded-lg shadow-xl mt-2 right-0 p-2 w-48 animate-fade-in">
                                <a href="<?= $base_url ?>/views/tai-khoan.php" class="block px-2 py-1 hover:bg-hover-bg rounded transition-colors duration-300 no-underline">Tài khoản</a>
                                <a href="<?= $base_url ?>/views/following.php" class="block px-2 py-1 hover:bg-hover-bg rounded transition-colors duration-300 no-underline">Theo dõi</a>
                                <a href="<?= $base_url ?>/views/lich-su-doc.php" class="block px-2 py-1 hover:bg-hover-bg rounded transition-colors duration-300 no-underline">Lịch sử đọc</a>
                                <a href="<?= $base_url ?>/views/logout.php" class="block px-2 py-1 hover:bg-red-100 rounded text-red-600 transition-colors duration-300 no-underline">Đăng xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/views/login.php" class="bg-button-primary px-3 py-1 rounded-lg hover:bg-button-hover text-white font-medium transition-all duration-300 animate-pulse-glow no-underline">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Thanh Menu Phụ -->
    <nav class="bg-white shadow-md fixed w-full top-12 z-40 hidden lg:block">
        <div class="max-w-6xl mx-auto px-4 py-1 flex items-center justify-center space-x-6 pt-1">
            <a href="<?= $base_url ?>/views/truyen-moi.php" class="text-lg font-medium text-purple-900 hover:text-button-primary transition-all duration-300 hover:scale-105 no-underline">Truyện Mới</a>
            <a href="<?= $base_url ?>/views/hoan-thanh.php" class="text-lg font-medium text-purple-900 hover:text-button-primary transition-all duration-300 hover:scale-105 no-underline">Hoàn Thành</a>
            <a href="<?= $base_url ?>/views/dang-phat-hanh.php" class="text-lg font-medium text-purple-900 hover:text-button-primary transition-all duration-300 hover:scale-105 no-underline">Đang Phát Hành</a>
            <a href="<?= $base_url ?>/views/sap-ra-mat.php" class="text-lg font-medium text-purple-900 hover:text-button-primary transition-all duration-300 hover:scale-105 no-underline">Sắp Ra Mắt</a>
            <div class="relative group">
                <a href="#" class="text-lg font-medium text-purple-900 hover:text-button-primary transition-all duration-300 hover:scale-105 no-underline">Thể Loại</a>
                <div class="absolute hidden group-hover:block bg-white text-gray-800 rounded-lg shadow-xl mt-2 p-3 w-64 max-h-80 overflow-y-auto animate-fade-in">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= $base_url ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="block px-2 py-1 hover:bg-hover-bg rounded transition-colors duration-300 no-underline">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="#" class="block px-2 py-1 text-gray-500 no-underline">Không có thể loại</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <?php if ($isIndexPage): ?>
        <div class="max-w-6xl mx-auto px-4 mt-24 fade-in">
            <div id="premiumCarousel" class="carousel slide premium-carousel" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($storySlugs as $index => $slug): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="img/slide/image<?= $index + 1 ?>.jpg" class="d-block w-100" alt="<?= htmlspecialchars($stories[$slug]['name']) ?>">
                            <div class="carousel-caption">
                                <h5><?= htmlspecialchars($stories[$slug]['name']) ?></h5>
                                <a href="views/truyen-detail.php?slug=<?= urlencode($slug) ?>" class="btn btn-xem-thong-tin no-underline">Xem thông tin</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#premiumCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#premiumCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden bg-gradient-to-b from-purple-800 to-teal-800 text-accent px-6 py-6 fixed top-12 left-0 w-full z-50 animate-slide-down">
        <form method="GET" action="<?= $base_url ?>/views/tim-kiem.php" class="flex mb-4">
            <input type="text" name="keyword" placeholder="Tìm kiếm..." required class="px-4 py-2 rounded-l-lg border-none text-gray-700 w-full text-lg transition-all duration-300">
            <button type="submit" class="bg-button-primary px-4 py-2 rounded-r-lg hover:bg-button-hover text-white text-lg transition-all duration-300 animate-pulse-glow">
                <i class="fas fa-search"></i>
            </button>
        </form>
        <a href="<?= $base_url ?>/views/truyen-moi.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Truyện Mới</a>
        <a href="<?= $base_url ?>/views/hoan-thanh.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Hoàn Thành</a>
        <a href="#" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline" id="mobile-categories-toggle">Thể Loại</a>
        <div id="mobile-categories" class="hidden pl-6 space-y-2 mt-2 max-h-60 overflow-y-auto">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="<?= $base_url ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']) ?>" class="block text-lg text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-1 no-underline">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="#" class="block text-gray-500 text-lg py-1 no-underline">Không có thể loại</a>
            <?php endif; ?>
        </div>
        <a href="<?= $base_url ?>/views/dang-phat-hanh.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Đang Phát Hành</a>
        <a href="<?= $base_url ?>/views/sap-ra-mat.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Sắp Ra Mắt</a>
        <div class="space-y-2 mt-4">
            <?php if (isset($_SESSION['user']['user_id'])): ?>
                <a href="<?= $base_url ?>/views/tai-khoan.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Tài khoản</a>
                <a href="<?= $base_url ?>/views/following.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Theo Dõi</a>
                <a href="<?= $base_url ?>/views/lich-su-doc.php" class="block text-xl text-white hover:text-button-primary transition-all duration-300 hover:scale-105 py-2 no-underline">Lịch Sử Đọc</a>
                <a href="<?= $base_url ?>/views/logout.php" class="block text-xl text-white hover:text-red-200 transition-all duration-300 hover:scale-105 py-2 no-underline">Đăng xuất</a>
            <?php else: ?>
                <a href="<?= $base_url ?>/views/login.php" class="block bg-button-primary px-6 py-3 rounded-lg hover:bg-button-hover text-white text-lg font-medium transition-all duration-300 animate-pulse-glow no-underline">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('hamburger').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('animate-slide-down');
                setTimeout(() => mobileMenu.classList.remove('animate-slide-down'), 300);
            }
        });

        document.getElementById('mobile-categories-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('mobile-categories').classList.toggle('hidden');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>