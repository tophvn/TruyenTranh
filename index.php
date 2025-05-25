<?php
include('config/database.php');
session_start();

function checkDDoSProtection() {
    $ip = $_SERVER['REMOTE_ADDR']; // Lấy địa chỉ IP của người dùng
    $cacheDir = 'cache/';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
    $requestLog = $cacheDir . 'ddos_' . md5($ip) . '.txt';
    $threshold = 50; // Ngưỡng tối đa request/phút
    $blockDuration = 600; // Thời gian chặn (10 phút)
    $timeWindow = 60; // Cửa sổ thời gian (1 phút)

    // Kiểm tra nếu IP bị chặn
    if (file_exists($requestLog . '_block')) {
        $blockTime = file_get_contents($requestLog . '_block');
        if ((time() - $blockTime) < $blockDuration) {
            header('HTTP/1.1 403 Forbidden');
            die('Quá nhiều yêu cầu. Vui lòng thử lại sau ' . ($blockDuration - (time() - $blockTime)) . ' giây.');
        } else {
            unlink($requestLog . '_block'); 
            @unlink($requestLog); 
        }
    }

    // Đọc log yêu cầu
    $requests = file_exists($requestLog) ? unserialize(file_get_contents($requestLog)) : [];
    $currentTime = time();

    // Lọc các request trong cửa sổ thời gian
    $requests = array_filter($requests, function($time) use ($currentTime, $timeWindow) {
        return ($currentTime - $time) < $timeWindow;
    });

    // Thêm request mới
    $requests[] = $currentTime;
    file_put_contents($requestLog, serialize($requests));

    // Kiểm tra ngưỡng
    if (count($requests) > $threshold) {
        file_put_contents($requestLog . '_block', $currentTime); // Chặn IP
        header('HTTP/1.1 403 Forbidden');
        die('Quá nhiều yêu cầu. Vui lòng thử lại sau ' . $blockDuration . ' giây.');
    }
}

// Gọi hàm kiểm tra DDoS
checkDDoSProtection();
// Đường dẫn tệp lưu ngày reset cuối cùng
$lastResetFile = 'cache/last_reset.txt';

// Hàm kiểm tra và reset daily_views
function resetDailyViews($conn, $lastResetFile) {
    $currentDate = date('Y-m-d');
    $lastResetDate = file_exists($lastResetFile) ? file_get_contents($lastResetFile) : '1970-01-01';

    if ($currentDate !== $lastResetDate) {
        $query = "UPDATE truyen SET daily_views = 0";
        if ($conn->query($query)) {
            file_put_contents($lastResetFile, $currentDate);
            error_log("Daily views đã được reset vào $currentDate");
        } else {
            error_log("Lỗi khi reset daily_views: " . $conn->error);
        }
    }
}

resetDailyViews($conn, $lastResetFile);

// Hàm lấy dữ liệu từ API với cache
function getCachedApiData($url, $cacheKey, $ttl = 3600) {
    $cacheDir = 'cache/';
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
    $cacheFile = $cacheDir . md5($cacheKey) . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $curlData = curl_exec($ch);
    curl_close($ch);
    if ($curlData) file_put_contents($cacheFile, $curlData);
    return json_decode($curlData, true);
}

// Hàm định dạng lượt xem
function formatViews($views) {
    if (!is_numeric($views) || $views === null) return '0';
    if ($views >= 1000000) return number_format($views / 1000000, 1, '.', '') . 'M';
    elseif ($views >= 1000) return number_format($views / 1000, 1, '.', '') . 'K';
    return number_format($views);
}

// Hàm tính thời gian trước
function timeAgo($dateString) {
    if (empty($dateString)) return 'Chưa cập nhật';
    $updateTime = new DateTime($dateString);
    $currentTime = new DateTime();
    $interval = $currentTime->diff($updateTime);
    if ($interval->y > 0) return $interval->y . ' năm trước';
    elseif ($interval->m > 0) return $interval->m . ' tháng trước';
    elseif ($interval->d > 0) return $interval->d . ' ngày trước';
    elseif ($interval->h > 0) return $interval->h . ' giờ trước';
    elseif ($interval->i > 0) return $interval->i . ' phút trước';
    else return 'Vừa xong';
}

// Lấy dữ liệu từ API trang chủ
$api_url = "https://otruyenapi.com/v1/api/home";
$data = getCachedApiData($api_url, 'home_data');
$truyenList = $data['data']['items'] ?? [];

$slugs = array_column($truyenList, 'slug');
function getAllViews($slugs) {
    global $conn;
    if (empty($slugs)) return [];
    $placeholders = implode(',', array_fill(0, count($slugs), '?'));
    $stmt = $conn->prepare("SELECT slug, views, daily_views FROM truyen WHERE slug IN ($placeholders)");
    $stmt->bind_param(str_repeat('s', count($slugs)), ...$slugs);
    $stmt->execute();
    $result = $stmt->get_result();
    $views = [];
    while ($row = $result->fetch_assoc()) {
        $views[$row['slug']] = ['views' => $row['views'] ?? 0, 'daily_views' => $row['daily_views'] ?? 0];
    }
    $stmt->close();
    return $views;
}

$viewsData = getAllViews($slugs);
foreach ($truyenList as &$truyen) {
    $truyen['views'] = $viewsData[$truyen['slug']]['views'] ?? 0;
    $truyen['daily_views'] = $viewsData[$truyen['slug']]['daily_views'] ?? 0;
}
unset($truyen);

// Hàm lấy top truyện xem nhiều
function getMostViewedTruyen($period = 'total', $limit = 9) {
    global $conn;
    $topTruyen = [];
    
    if ($period === 'day') {
        // Lấy lượt xem trong ngày hôm nay
        $query = "SELECT slug, name, thumb_url, updated_at, views, daily_views 
                  FROM truyen 
                  WHERE daily_views > 0 
                  ORDER BY daily_views DESC 
                  LIMIT $limit";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $topTruyen[] = $row;
        }
    } elseif ($period === 'week') {
        // Lấy tổng lượt xem trong 7 ngày qua
        $query = "SELECT t.slug, t.name, t.thumb_url, t.updated_at, t.views, 
                         COALESCE(SUM(vl.views), t.daily_views) AS weekly_views 
                  FROM truyen t 
                  LEFT JOIN view_logs vl ON t.slug = vl.truyen_slug 
                  WHERE vl.view_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                  GROUP BY t.slug, t.name, t.thumb_url, t.updated_at, t.views 
                  ORDER BY weekly_views DESC 
                  LIMIT $limit";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $topTruyen[] = $row;
        }
        
        // Nếu không đủ dữ liệu từ view_logs, bổ sung từ views tổng
        if (count($topTruyen) < $limit) {
            $remaining = $limit - count($topTruyen);
            $excludeSlugs = array_column($topTruyen, 'slug');
            $placeholders = $excludeSlugs ? 'AND slug NOT IN (' . implode(',', array_fill(0, count($excludeSlugs), '?')) . ')' : '';
            $stmt = $conn->prepare("SELECT slug, name, thumb_url, updated_at, views, daily_views 
                                  FROM truyen 
                                  WHERE 1 $placeholders 
                                  ORDER BY views DESC 
                                  LIMIT $remaining");
            if ($excludeSlugs) $stmt->bind_param(str_repeat('s', count($excludeSlugs)), ...$excludeSlugs);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $topTruyen[] = $row;
            }
            $stmt->close();
        }
    } else { // total
        // Lấy tổng lượt xem
        $query = "SELECT slug, name, thumb_url, updated_at, views, daily_views 
                  FROM truyen 
                  ORDER BY views DESC 
                  LIMIT $limit";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $topTruyen[] = $row;
        }
    }
    return array_slice($topTruyen, 0, $limit);
}

// Hàm lấy truyện ngẫu nhiên theo thể loại
function getRandomTruyenByCategory($categorySlug, $limit = 10) {
    $api_url = "https://otruyenapi.com/v1/api/the-loai/{$categorySlug}";
    $data = getCachedApiData($api_url, "category_{$categorySlug}", 3600);
    $truyenList = $data['data']['items'] ?? [];
    
    if (count($truyenList) < $limit) {
        $totalPages = ceil($data['data']['params']['pagination']['totalItems'] / $data['data']['params']['pagination']['totalItemsPerPage']);
        for ($page = 2; $page <= min($totalPages, 3); $page++) {
            $pageData = getCachedApiData("{$api_url}?page={$page}", "category_{$categorySlug}_page_{$page}");
            $truyenList = array_merge($truyenList, $pageData['data']['items'] ?? []);
            if (count($truyenList) >= $limit) break;
        }
    }
    
    $slugs = array_column($truyenList, 'slug');
    $viewsData = getAllViews($slugs);
    foreach ($truyenList as &$truyen) {
        $truyen['views'] = $viewsData[$truyen['slug']]['views'] ?? 0;
        $truyen['daily_views'] = $viewsData[$truyen['slug']]['daily_views'] ?? 0;
    }
    unset($truyen);
    
    shuffle($truyenList);
    return array_slice($truyenList, 0, $limit);
}

$storySlugs = ['cau-lac-bo-sieu-cap-ve-nha', 'chainsawman-phan-2', 'ruri-dragon', 'dai-chien-nguoi-khong-lo'];
$stories = [];
foreach ($storySlugs as $slug) {
    $storyData = getCachedApiData("https://otruyenapi.com/v1/api/truyen-tranh/$slug", "story_$slug");
    $story = $storyData['data']['item'] ?? ['slug' => $slug, 'name' => 'Không tìm thấy', 'thumb_url' => '', 'chaptersLatest' => [], 'updatedAt' => null];
    $viewsData = getAllViews([$slug]);
    $story['views'] = $viewsData[$slug]['views'] ?? 0;
    $story['daily_views'] = $viewsData[$slug]['daily_views'] ?? 0;
    $stories[] = $story;
}

// Hàm lấy truyện sắp ra mắt
function getUpcomingTruyen($limit = 20) {
    $api_url = "https://otruyenapi.com/v1/api/danh-sach/sap-ra-mat?page=1";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $truyenList = $data['data']['items'] ?? [];
    
    // Nếu không đủ 20 truyện, lấy thêm từ trang tiếp theo
    if (count($truyenList) < $limit) {
        $totalPages = ceil($data['data']['params']['pagination']['totalItems'] / $data['data']['params']['pagination']['totalItemsPerPage']);
        for ($page = 2; $page <= min($totalPages, 3); $page++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://otruyenapi.com/v1/api/danh-sach/sap-ra-mat?page=$page");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            curl_close($ch);
            $pageData = json_decode($response, true);
            $truyenList = array_merge($truyenList, $pageData['data']['items'] ?? []);
            if (count($truyenList) >= $limit) break;
        }
    }
    
    // Xáo trộn thứ tự ngẫu nhiên
    shuffle($truyenList);
    return array_slice($truyenList, 0, $limit); // Lấy tối đa $limit truyện
}
$upcomingTruyen = getUpcomingTruyen(20); // Lấy 20 truyện sắp ra mắt

$mostViewedTotal = getMostViewedTruyen('total');
$mostViewedDaily = getMostViewedTruyen('day');
$mostViewedWeekly = getMostViewedTruyen('week');
$mangaList = getRandomTruyenByCategory('manga');
$manhwaList = getRandomTruyenByCategory('manhwa');
$manhuaList = getRandomTruyenByCategory('manhua');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="đọc truyện tranh, manga online, manhwa miễn phí, manhua mới nhất, truyện hay, TRUYEN0HAY">
    <meta name="description" content="TRUYEN0HAY - Đọc truyện tranh miễn phí, cập nhật manga, manhwa, manhua mới nhất mỗi ngày. Khám phá kho truyện đa dạng, chất lượng cao!">
    <meta property="og:title" content="TRUYEN0HAY - Đọc Truyện Tranh Miễn Phí Online">
    <meta property="og:description" content="Đọc truyện tranh miễn phí tại TRUYEN0HAY. Cập nhật manga, manhwa, manhua mới nhất mỗi ngày. Khám phá ngay!">
    <meta property="og:image" content="https://i.ibb.co/sB5C6hQ/logo.png">
    <meta property="og:url" content="https://truyen0hay.site">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="TRUYEN0HAY - Đọc Truyện Tranh Miễn Phí Online">
    <meta name="twitter:description" content="Đọc truyện tranh miễn phí tại TRUYEN0HAY. Cập nhật manga, manhwa, manhua mới nhất mỗi ngày.">
    <meta name="twitter:image" content="https://i.ibb.co/sB5C6hQ/logo.png">
    <meta name="robots" content="index, follow">
    <meta name="author" content="TRUYEN0HAY">
    <meta name="revisit-after" content="1 days">
    <meta name="google-adsense-account" content="ca-pub-7781390490354736">
    <link href="https://i.ibb.co/sB5C6hQ/logo.png" rel="icon" type="image/png">
    <title>Trang Chủ - TRUYEN0HAY - Đọc Truyện Tranh Miễn Phí Online</title>
    <link rel="preload" href="https://i.ibb.co/sB5C6hQ/logo.png" as="image">
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "TRUYEN0HAY",
        "url": "https://truyen0hay.site",
        "description": "Đọc truyện tranh miễn phí tại TRUYEN0HAY. Cập nhật manga, manhwa, manhua mới nhất mỗi ngày.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://truyen0hay.site/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <style>
        .update-tag { background-color: #00b7eb; color: #ffffff; font-size: 12px; font-weight: bold; padding: 2px 6px; border-radius: 3px; }
        .badge-18plus { position: absolute; top: 8px; right: 8px; background-color: #ff0000; color: #ffffff; font-size: 12px; font-weight: bold; padding: 2px 6px; border-radius: 3px; z-index: 10; }
        .time-tag { position: absolute; top: 8px; left: 8px; background-color: #0099FF; color: #ffffff; font-size: 12px; font-weight: bold; padding: 2px 6px; border-radius: 3px; z-index: 10; }
        .horizontal-slider { overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none; }
        .horizontal-slider::-webkit-scrollbar { display: none; }
        .slide-item { scroll-snap-align: start; flex-shrink: 0; }
        .tab-button { padding: 8px 16px; cursor: pointer; transition: all 0.3s; }
        .tab-button.active { background-color: #10b981; color: white; }
    </style>
</head>
<body class="bg-gray-900 text-white font-poppins">
    <?php include 'includes/header.php'; ?>

    <!-- Phần trên với carousel và sidebar -->
    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8 pt-16">
        <div class="lg:w-3/4">
            <!-- Carousel -->
            <div 
                x-data="{ 
                    currentSlide: 0,
                    init() {
                        this.startAutoSlide();
                    },
                    startAutoSlide() {
                        setInterval(() => {
                            this.currentSlide = (this.currentSlide + 1) % <?= count($stories) ?>;
                        }, 5000);
                    }
                }" 
                class="relative mb-8"
            >
                <div class="overflow-hidden rounded-lg">
                    <div class="flex transition-transform duration-500" :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                        <?php 
                        $imagePaths = [
                            'img/slide/image1.jpg',
                            'img/slide/image2.jpg',
                            'img/slide/image3.jpg',
                            'img/slide/image4.jpg'
                        ];
                        foreach ($stories as $i => $story):
                            if ($i >= count($storySlugs)) break;
                        ?>
                            <div class="min-w-full relative">
                                <img 
                                    src="<?= $imagePaths[$i] ?>" 
                                    alt="<?= htmlspecialchars($story['name']) ?>" 
                                    class="w-full h-auto object-cover rounded-lg" 
                                    style="aspect-ratio: 16/9; max-height: 500px;"
                                >
                                <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black to-transparent flex items-end p-6">
                                    <div class="flex flex-col items-start md:items-start w-full">
                                        <h2 class="text-xl md:text-2xl font-bold text-white text-center md:text-left w-full"><?= htmlspecialchars($story['name']) ?></h2>
                                        <a 
                                            href="views/truyen-tranh/<?= urlencode($story['slug']) ?>" 
                                            class="mt-2 inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors duration-300 self-center md:self-start"
                                        >
                                            Đọc ngay
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button 
                    @click="currentSlide = (currentSlide - 1 + <?= count($stories) ?>) % <?= count($stories) ?>" 
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition-colors duration-300"
                >
                    <i class="fas fa-chevron-left text-white"></i>
                </button>
                <button 
                    @click="currentSlide = (currentSlide + 1) % <?= count($stories) ?>" 
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition-colors duration-300"
                >
                    <i class="fas fa-chevron-right text-white"></i>
                </button>
            </div>

            <!-- Truyện mới cập nhật -->
            <h4 class="text-2xl font-semibold mb-6 text-center">TRUYỆN TRANH MỚI CẬP NHẬT</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-8" x-data="{ visible: false }" x-init="$nextTick(() => visible = true)" :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'" x-transition.duration.500ms>
                <?php foreach ($truyenList as $truyen): ?>
                    <div class="bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="relative block">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                 class="w-full object-cover" 
                                 alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                 loading="lazy" 
                                 style="aspect-ratio: 3/4; height: 220px;">
                            <?php 
                            foreach ($truyen['category'] ?? [] as $cat) {
                                if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                    echo '<span class="badge-18plus">18+</span>';
                                    break;
                                }
                            }
                            ?>
                            <span class="time-tag"><?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                        </a>
                        <div class="p-2">
                            <h5 class="text-base font-semibold truncate text-white mb-1 text-center">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                            </h5>
                            <div class="flex justify-between items-center text-base text-gray-300 mb-1">
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                    <a href="views/dang-phat-hanh" class="flex flex-col items-center justify-center h-full text-center p-4">
                        <i class="fas fa-arrow-right text-3xl text-green-500 mb-2"></i>
                        <span class="text-base font-semibold text-white">Xem thêm</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar (chỉ ở khu vực trên) -->
        <div class="lg:w-1/4">
            <!-- Bảng Xếp Hạng -->
            <div class="bg-gray-800 p-4 rounded-lg mb-6" x-data="{ activeTab: 'day' }">
                <h4 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-trophy mr-2 flex-shrink-0"></i> 
                    BẢNG XẾP HẠNG
                </h4>
                <div class="flex justify-around mb-4">
                    <button class="tab-button" :class="{ 'active': activeTab === 'day' }" @click="activeTab = 'day'">Ngày</button>
                    <button class="tab-button" :class="{ 'active': activeTab === 'week' }" @click="activeTab = 'week'">Tuần</button>
                    <button class="tab-button" :class="{ 'active': activeTab === 'total' }" @click="activeTab = 'total'">ALL</button>
                </div>
                <!-- Tab Ngày -->
                <div x-show="activeTab === 'day'" x-transition.opacity class="space-y-3">
                    <?php foreach ($mostViewedDaily as $index => $truyen): ?>
                        <div class="flex items-center bg-gray-700 p-2 rounded-lg hover:bg-gray-600 transition-colors duration-300 overflow-hidden">
                            <span class="text-yellow-400 font-bold mr-2 text-sm w-6 flex-shrink-0"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                class="w-10 h-14 object-cover rounded mr-2 flex-shrink-0" 
                                alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                loading="lazy">
                            <div class="flex-1 min-w-0">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" 
                                class="text-sm font-medium text-white hover:text-yellow-400 transition-colors duration-200 truncate block" 
                                title="<?= htmlspecialchars($truyen['name']) ?>">
                                    <?= htmlspecialchars($truyen['name']) ?>
                                </a>
                                <p class="text-xs text-gray-400 truncate"><i class="fas fa-eye mr-1"></i> <?= formatViews($truyen['daily_views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Tab Tuần -->
                <div x-show="activeTab === 'week'" x-transition.opacity class="space-y-3">
                    <?php foreach ($mostViewedWeekly as $index => $truyen): ?>
                        <div class="flex items-center bg-gray-700 p-2 rounded-lg hover:bg-gray-600 transition-colors duration-300 overflow-hidden">
                            <span class="text-yellow-400 font-bold mr-2 text-sm w-6 flex-shrink-0"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                class="w-10 h-14 object-cover rounded mr-2 flex-shrink-0" 
                                alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                loading="lazy">
                            <div class="flex-1 min-w-0">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" 
                                class="text-sm font-medium text-white hover:text-yellow-400 transition-colors duration-200 truncate block" 
                                title="<?= htmlspecialchars($truyen['name']) ?>">
                                    <?= htmlspecialchars($truyen['name']) ?>
                                </a>
                                <p class="text-xs text-gray-400 truncate"><i class="fas fa-eye mr-1"></i> <?= formatViews($truyen['weekly_views'] ?? $truyen['views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Tab Tổng -->
                <div x-show="activeTab === 'total'" x-transition.opacity class="space-y-3">
                    <?php foreach ($mostViewedTotal as $index => $truyen): ?>
                        <div class="flex items-center bg-gray-700 p-2 rounded-lg hover:bg-gray-600 transition-colors duration-300 overflow-hidden">
                            <span class="text-yellow-400 font-bold mr-2 text-sm w-6 flex-shrink-0"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                class="w-10 h-14 object-cover rounded mr-2 flex-shrink-0" 
                                alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                loading="lazy">
                            <div class="flex-1 min-w-0">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" 
                                class="text-sm font-medium text-white hover:text-yellow-400 transition-colors duration-200 truncate block" 
                                title="<?= htmlspecialchars($truyen['name']) ?>">
                                    <?= htmlspecialchars($truyen['name']) ?>
                                </a>
                                <p class="text-xs text-gray-400 truncate"><i class="fas fa-eye mr-1"></i> <?= formatViews($truyen['views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Truyện Xem Gần Đây -->
            <div class="bg-gray-800 p-4 rounded-lg mb-6">
                <a href="views/lich-su-doc.php" class="block">
                    <h4 class="text-lg font-semibold mb-4 hover:text-green-500 transition-colors duration-300 flex items-center">
                        <i class="fas fa-history mr-2 flex-shrink-0"></i>
                        TRUYỆN XEM GẦN ĐÂY
                    </h4>
                </a>
                <div id="recentlyViewedStories" class="space-y-3">
                    <p class="text-center text-gray-400 text-sm">Bạn chưa xem truyện nào gần đây.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">

        <!-- Manga Slide -->
        <h4 class="text-2xl font-semibold mb-6 text-center">MANGA</h4>
        <div class="horizontal-slider flex gap-3 mb-8">
            <?php if (empty($mangaList)): ?>
                <div class="text-center w-full text-gray-400">Không có truyện Manga nào để hiển thị.</div>
            <?php else: ?>
                <?php foreach ($mangaList as $truyen): ?>
                    <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40">
                        <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="relative block">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                 class="w-full object-cover" 
                                 alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                 loading="lazy" 
                                 style="aspect-ratio: 3/4; height: 220px;">
                            <?php 
                            foreach ($truyen['category'] ?? [] as $cat) {
                                if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                    echo '<span class="badge-18plus">18+</span>';
                                    break;
                                }
                            }
                            ?>
                            <span class="time-tag"><?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                        </a>
                        <div class="p-2">
                            <h5 class="text-sm font-semibold truncate text-white mb-1 text-center">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                            </h5>
                            <div class="flex justify-between items-center text-xs text-gray-300 mb-1">
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40 flex items-center justify-center">
                    <a href="views/truyen-theo-the-loai/manga" class="flex flex-col items-center justify-center h-full text-center p-4">
                        <i class="fas fa-arrow-right text-3xl text-green-500 mb-2"></i>
                        <span class="text-sm font-semibold text-white">Xem thêm</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Manhwa Slide -->
        <h4 class="text-2xl font-semibold mb-6 text-center">MANHWA</h4>
        <div class="horizontal-slider flex gap-3 mb-8">
            <?php if (empty($manhwaList)): ?>
                <div class="text-center w-full text-gray-400">Không có truyện Manhwa nào để hiển thị.</div>
            <?php else: ?>
                <?php foreach ($manhwaList as $truyen): ?>
                    <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40">
                        <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="relative block">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                 class="w-full object-cover" 
                                 alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                 loading="lazy" 
                                 style="aspect-ratio: 3/4; height: 220px;">
                            <?php 
                            foreach ($truyen['category'] ?? [] as $cat) {
                                if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                    echo '<span class="badge-18plus">18+</span>';
                                    break;
                                }
                            }
                            ?>
                            <span class="time-tag"><?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                        </a>
                        <div class="p-2">
                            <h5 class="text-sm font-semibold truncate text-white mb-1 text-center">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                            </h5>
                            <div class="flex justify-between items-center text-xs text-gray-300 mb-1">
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40 flex items-center justify-center">
                    <a href="views/truyen-theo-the-loai/manhwa" class="flex flex-col items-center justify-center h-full text-center p-4">
                        <i class="fas fa-arrow-right text-3xl text-green-500 mb-2"></i>
                        <span class="text-sm font-semibold text-white">Xem thêm</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Manhua Slide -->
        <h4 class="text-2xl font-semibold mb-6 text-center">MANHUA</h4>
        <div class="horizontal-slider flex gap-3 mb-8">
            <?php if (empty($manhuaList)): ?>
                <div class="text-center w-full text-gray-400">Không có truyện Manhua nào để hiển thị.</div>
            <?php else: ?>
                <?php foreach ($manhuaList as $truyen): ?>
                    <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40">
                        <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="relative block">
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                 class="w-full object-cover" 
                                 alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                 loading="lazy" 
                                 style="aspect-ratio: 3/4; height: 220px;">
                            <?php 
                            foreach ($truyen['category'] ?? [] as $cat) {
                                if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                    echo '<span class="badge-18plus">18+</span>';
                                    break;
                                }
                            }
                            ?>
                            <span class="time-tag"><?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                        </a>
                        <div class="p-2">
                            <h5 class="text-sm font-semibold truncate text-white mb-1 text-center">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                            </h5>
                            <div class="flex justify-between items-center text-xs text-gray-300 mb-1">
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="slide-item bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 w-40 flex items-center justify-center">
                    <a href="views/truyen-theo-the-loai/manhua" class="flex flex-col items-center justify-center h-full text-center p-4">
                        <i class="fas fa-arrow-right text-3xl text-green-500 mb-2"></i>
                        <span class="text-sm font-semibold text-white">Xem thêm</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Stories Slide -->
        <div class="container mx-auto px-4 py-8">
            <h4 class="text-2xl font-semibold mb-6 text-center flex items-center justify-center">
                <i class="fas fa-clock mr-2"></i> TRUYỆN SẮP RA MẮT
            </h4>
            <div class="relative overflow-hidden">
                <div id="upcomingSlider" class="flex transition-transform duration-300 ease-in-out" style="will-change: transform;">
                    <?php foreach ($upcomingTruyen as $truyen): ?>
                        <div class="flex-shrink-0 w-40 mx-2">
                            <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="block">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                    alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                    class="w-full h-48 object-cover rounded-lg shadow-md hover:opacity-90 transition" 
                                    loading="lazy">
                            </a>
                            <div class="mt-2 text-center">
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" 
                                class="text-sm font-medium text-white hover:text-blue-400 transition-colors duration-200 block truncate" 
                                title="<?= htmlspecialchars($truyen['name']) ?>">
                                    <?= htmlspecialchars($truyen['name']) ?>
                                </a>
                                <span><i class="fas fa-bookmark mr-1 text-green-500"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'Chưa có') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Nút điều khiển trái/phải -->
                <button id="prevSlide" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition-colors duration-300 opacity-75 hover:opacity-100">
                    <i class="fas fa-chevron-left text-white"></i>
                </button>
                <button id="nextSlide" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition-colors duration-300 opacity-75 hover:opacity-100">
                    <i class="fas fa-chevron-right text-white"></i>
                </button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    function renderRecentlyViewed() {
        const recentlyViewedContainer = document.getElementById('recentlyViewedStories');
        let readHistory = JSON.parse(localStorage.getItem('readHistory')) || [];
        
        recentlyViewedContainer.innerHTML = ''; // Xóa nội dung cũ

        if (readHistory.length === 0) {
            recentlyViewedContainer.innerHTML = '<p class="text-center text-gray-400 text-sm">Bạn chưa xem truyện nào gần đây.</p>';
            return;
        }

        readHistory.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        const recentStories = readHistory.slice(0, 7);

        recentStories.forEach((chapter, index) => {
            const storyItem = `
                <div class="flex items-center bg-gray-700 p-2 rounded-lg hover:bg-gray-600 transition-colors duration-300 overflow-hidden">
                    <span class="text-green-500 font-bold mr-2 text-sm w-6 flex-shrink-0">${index + 1}</span>
                    <img src="${chapter.chapter_image}" 
                         class="w-10 h-14 object-cover rounded mr-2 flex-shrink-0" 
                         alt="${chapter.chapter_story_name}" 
                         loading="lazy">
                    <div class="flex-1 min-w-0">
                        <a href="views/truyen-tranh/${chapter.chapter_link.split('/')[2]}" 
                           class="text-sm font-medium text-white hover:text-green-500 transition-colors duration-200 truncate block" 
                           title="${chapter.chapter_story_name}">
                            ${chapter.chapter_story_name}
                        </a>
                        <p class="text-xs text-gray-400 truncate"><i class="fas fa-bookmark mr-1"></i>${chapter.chapter_name}</p>
                    </div>
                </div>
            `;
            recentlyViewedContainer.insertAdjacentHTML('beforeend', storyItem);
        });
    }

    document.addEventListener('DOMContentLoaded', renderRecentlyViewed);

    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.getElementById('upcomingSlider');
        const prevButton = document.getElementById('prevSlide');
        const nextButton = document.getElementById('nextSlide');
        const items = slider.children;
        const itemWidth = items[0].offsetWidth + 16; // 16 là tổng margin左右 (mx-2)
        const totalItems = items.length;
        let currentIndex = 0;
        let autoSlide;

        slider.innerHTML += slider.innerHTML;

        function updateSlide() {
            slider.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        }

        function nextSlide() {
            currentIndex++;
            if (currentIndex >= totalItems) {
                currentIndex = 0;
                slider.style.transition = 'none'; 
                updateSlide();
                requestAnimationFrame(() => {
                    slider.style.transition = 'transform 0.3s ease-in-out';
                });
            }
            updateSlide();
        }

        function prevSlide() {
            currentIndex--;
            if (currentIndex < 0) {
                currentIndex = totalItems - 1;
                slider.style.transition = 'none'; 
                updateSlide();
                requestAnimationFrame(() => {
                    slider.style.transition = 'transform 0.3s ease-in-out';
                });
            }
            updateSlide();
        }

        function startAutoSlide() {
            autoSlide = setInterval(nextSlide, 2000);
        }

        function stopAutoSlide() {
            clearInterval(autoSlide);
        }
        startAutoSlide();

        nextButton.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide(); 
        });

        prevButton.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide(); 
        });
        slider.parentElement.addEventListener('mouseenter', stopAutoSlide);
        slider.parentElement.addEventListener('mouseleave', startAutoSlide);
    });
    </script>
</body>
</html>
