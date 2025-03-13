<?php
include('config/database.php');
session_start();

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
    if ($views >= 1000000) return number_format($views / 1000000, 1, '.', '') . 'M';
    elseif ($views >= 1000) return number_format($views / 1000, 1, '.', '') . 'K';
    return $views;
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

// Tạo mảng slug => truyen
$truyenListBySlug = array_column($truyenList, null, 'slug');
$slugs = array_keys($truyenListBySlug);

// Lấy lượt xem từ database
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
    return $views;
}

$viewsData = getAllViews($slugs);
foreach ($truyenListBySlug as $slug => &$truyen) {
    $truyen['views'] = $viewsData[$slug]['views'] ?? 0;
    $truyen['daily_views'] = $viewsData[$slug]['daily_views'] ?? 0;
}
unset($truyen);

// Hàm lấy top truyện xem nhiều (sửa cho Top Ngày)
function getMostViewedTruyen($period = 'total', $limit = 9) {
    global $conn, $truyenListBySlug;

    if ($period === 'day') {
        $query = "SELECT slug, name, thumb_url, updated_at, views, daily_views 
                  FROM truyen 
                  WHERE daily_views > 0 
                  ORDER BY daily_views DESC 
                  LIMIT $limit";
        $result = $conn->query($query);
        $topTruyen = [];
        while ($row = $result->fetch_assoc()) {
            $truyen = $truyenListBySlug[$row['slug']] ?? [];
            $topTruyen[] = [
                'slug' => $row['slug'],
                'name' => $row['name'],
                'thumb_url' => $row['thumb_url'],
                'updatedAt' => $truyen['updatedAt'] ?? $row['updated_at'],
                'chaptersLatest' => $truyen['chaptersLatest'] ?? [],
                'views' => $row['views'] ?? 0,
                'daily_views' => $row['daily_views'] ?? 0
            ];
        }

        if (count($topTruyen) < $limit) {
            $remaining = $limit - count($topTruyen);
            $excludeSlugs = array_column($topTruyen, 'slug');
            $placeholders = $excludeSlugs ? 'WHERE slug NOT IN (' . implode(',', array_fill(0, count($excludeSlugs), '?')) . ')' : '';
            $fallbackQuery = "SELECT slug, name, thumb_url, updated_at, views, daily_views 
                             FROM truyen 
                             $placeholders 
                             ORDER BY views DESC 
                             LIMIT $remaining";
            $stmt = $conn->prepare($fallbackQuery);
            if ($excludeSlugs) {
                $stmt->bind_param(str_repeat('s', count($excludeSlugs)), ...$excludeSlugs);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $truyen = $truyenListBySlug[$row['slug']] ?? [];
                $topTruyen[] = [
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                    'thumb_url' => $row['thumb_url'],
                    'updatedAt' => $truyen['updatedAt'] ?? $row['updated_at'],
                    'chaptersLatest' => $truyen['chaptersLatest'] ?? [],
                    'views' => $row['views'] ?? 0,
                    'daily_views' => $row['daily_views'] ?? 0
                ];
            }
        }
    } else {
        $query = "SELECT slug, name, thumb_url, updated_at, views, daily_views FROM truyen";
        if ($period === 'week') {
            $query .= " WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
        $query .= " ORDER BY views DESC LIMIT $limit";
        $result = $conn->query($query);
        $topTruyen = [];
        while ($row = $result->fetch_assoc()) {
            $truyen = $truyenListBySlug[$row['slug']] ?? [];
            $topTruyen[] = [
                'slug' => $row['slug'],
                'name' => $row['name'],
                'thumb_url' => $row['thumb_url'],
                'updatedAt' => $truyen['updatedAt'] ?? $row['updated_at'],
                'chaptersLatest' => $truyen['chaptersLatest'] ?? [],
                'views' => $row['views'] ?? 0,
                'daily_views' => $row['daily_views'] ?? 0
            ];
        }
    }

    return array_slice($topTruyen, 0, $limit);
}

// Hàm lấy 10 truyện ngẫu nhiên theo thể loại từ API
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
    $views = getAllViews($slugs);
    foreach ($truyenList as &$truyen) {
        $truyen['views'] = $views[$truyen['slug']]['views'] ?? 0;
        $truyen['daily_views'] = $views[$truyen['slug']]['daily_views'] ?? 0;
    }
    unset($truyen);

    shuffle($truyenList);
    return array_slice($truyenList, 0, $limit);
}

// Dữ liệu cho các phần
$storySlugs = ['cau-lac-bo-sieu-cap-ve-nha', 'chainsawman-phan-2', 'ruri-dragon', 'dai-chien-nguoi-khong-lo'];
$stories = [];
foreach ($storySlugs as $slug) {
    if (isset($truyenListBySlug[$slug])) {
        $stories[] = $truyenListBySlug[$slug];
    } else {
        // Nếu không có trong API home, lấy từ API chi tiết
        $storyData = getCachedApiData("https://otruyenapi.com/v1/api/truyen-tranh/$slug", "story_$slug");
        $storyItem = $storyData['data']['item'] ?? ['name' => 'Không tìm thấy', 'thumb_url' => '', 'chaptersLatest' => [], 'updatedAt' => null, 'views' => 0, 'daily_views' => 0];
        $stories[] = $storyItem;
    }
}

// Debug dữ liệu
echo "<script>console.log('Stories:', " . json_encode($stories) . ");</script>";

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
    <meta name="keywords" content="truyện tranh, manga, manhwa, manhua, đọc truyện miễn phí, TRUYENTRANHNET">
    <meta name="description" content="Đọc truyện tranh miễn phí tại TRUYENTRANHNET. Tìm kiếm manga, manhwa, manhua yêu thích và khám phá các bộ truyện mới nhất.">
    <meta property="og:title" content="TRUYENTRANHNET - ĐỌC TRUYỆN MIỄN PHÍ">
    <meta property="og:description" content="Đọc truyện tranh miễn phí tại TRUYENTRANHNET. Tìm kiếm manga, manhwa, manhua yêu thích và khám phá các bộ truyện mới nhất.">
    <meta property="og:image" content="https://www.truyentranhnet.free.nf/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.free.nf">
    <meta name="robots" content="index, follow">
    <link href="img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Đọc Truyện Miễn Phí</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8 pt-16">
        <div class="lg:w-3/4">
            <!-- Carousel -->
            <div x-data="{ currentSlide: 0 }" class="relative mb-8">
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
                            $slug = $storySlugs[$i];
                        ?>
                            <div class="min-w-full relative">
                                <img 
                                    src="<?= $imagePaths[$i] ?>" 
                                    alt="<?= htmlspecialchars($story['name']) ?>" 
                                    class="w-full h-auto object-cover rounded-lg" 
                                    style="aspect-ratio: 16/9; max-height: 500px;"
                                >
                                <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black to-transparent flex items-end p-6">
                                    <div>
                                        <h2 class="text-xl md:text-2xl font-bold text-white"><?= htmlspecialchars($story['name']) ?></h2>
                                        <a 
                                            href="views/truyen-tranh/<?= urlencode($slug) ?>" 
                                            class="mt-2 inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors duration-300"
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
                                <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views'] ?? 0) ?></span>
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
                                    <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views'] ?? 0) ?></span>
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
                                    <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views'] ?? 0) ?></span>
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
                                    <span><i class="fas fa-eye mr-1 text-yellow-400"></i> <?= formatViews($truyen['views'] ?? 0) ?></span>
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
        </div>
        <!-- Sidebar -->
        <div class="lg:w-1/4">
            <div class="bg-gray-800 p-4 rounded-lg mb-6" x-data="{ activeTab: 'total' }">
                <h4 class="text-lg font-semibold mb-4">BẢNG XẾP HẠNG</h4>
                <div class="flex justify-around mb-4">
                    <button class="tab-button" :class="{ 'active': activeTab === 'day' }" @click="activeTab = 'day'">Ngày</button>
                    <button class="tab-button" :class="{ 'active': activeTab === 'week' }" @click="activeTab = 'week'">Tuần</button>
                    <button class="tab-button" :class="{ 'active': activeTab === 'total' }" @click="activeTab = 'total'">ALL</button>
                </div>
                <!-- Tab Ngày -->
                <div x-show="activeTab === 'day'" x-transition.opacity>
                    <?php foreach ($mostViewedDaily as $index => $truyen): ?>
                        <div class="flex items-center mb-4 hover:bg-gray-700 p-2 rounded">
                            <span class="text-yellow-400 font-bold mr-2"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" class="w-12 h-16 object-cover rounded mr-3" alt="<?= htmlspecialchars($truyen['name']) ?>">
                            <div>
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="text-sm font-medium hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                                <p class="text-xs text-gray-400"><i class="fas fa-eye"></i> <?= formatViews($truyen['daily_views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Tab Tuần -->
                <div x-show="activeTab === 'week'" x-transition.opacity>
                    <?php foreach ($mostViewedWeekly as $index => $truyen): ?>
                        <div class="flex items-center mb-4 hover:bg-gray-700 p-2 rounded">
                            <span class="text-yellow-400 font-bold mr-2"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" class="w-12 h-16 object-cover rounded mr-3" alt="<?= htmlspecialchars($truyen['name']) ?>">
                            <div>
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="text-sm font-medium hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                                <p class="text-xs text-gray-400"><i class="fas fa-eye"></i> <?= formatViews($truyen['views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Tab Tổng -->
                <div x-show="activeTab === 'total'" x-transition.opacity>
                    <?php foreach ($mostViewedTotal as $index => $truyen): ?>
                        <div class="flex items-center mb-4 hover:bg-gray-700 p-2 rounded">
                            <span class="text-yellow-400 font-bold mr-2"><?= $index + 1 ?></span>
                            <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" class="w-12 h-16 object-cover rounded mr-3" alt="<?= htmlspecialchars($truyen['name']) ?>">
                            <div>
                                <a href="views/truyen-tranh/<?= urlencode($truyen['slug']) ?>" class="text-sm font-medium hover:text-green-500"><?= htmlspecialchars($truyen['name']) ?></a>
                                <p class="text-xs text-gray-400"><i class="fas fa-eye"></i> <?= formatViews($truyen['views']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>