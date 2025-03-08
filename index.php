<?php
include('config/database.php');

session_start();
$api_url = "https://otruyenapi.com/v1/api/home";
$isIndexPage = basename($_SERVER['PHP_SELF']) === 'index.php';

// Gọi API home để lấy danh sách truyện (cho phần "Truyện Mới Cập Nhật")
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$truyenList = (isset($data['data']) && !empty($data['data'])) ? $data['data']['items'] : [];

// Tạo mảng để tra cứu nhanh chaptersLatest từ API /home
$truyenListBySlug = [];
foreach ($truyenList as $truyen) {
    $truyenListBySlug[$truyen['slug']] = $truyen;
}

// Hàm lấy thông tin truyện từ API theo slug
function getStoryDetails($slug) {
    $api_url = "https://otruyenapi.com/v1/api/truyen-tranh/" . urlencode($slug);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['item'])) {
        return [
            'name' => $data['data']['item']['name'],
            'thumb_url' => $data['data']['item']['thumb_url'],
            'chaptersLatest' => $data['data']['item']['chapters_latest'] ?? [],
            'updatedAt' => $data['data']['item']['updated_at'] ?? null,
            'category' => $data['data']['item']['category'] ?? []
        ];
    }
    return ['name' => 'Không tìm thấy', 'thumb_url' => '', 'chaptersLatest' => [], 'updatedAt' => null, 'category' => []];
}

// Danh sách slug của các truyện bạn muốn hiển thị trong carousel
$storySlugs = ['cau-lac-bo-sieu-cap-ve-nha', 'chainsawman-phan-2', 'ruri-dragon', 'dai-chien-nguoi-khong-lo'];
$stories = [];
foreach ($storySlugs as $slug) {
    $stories[$slug] = getStoryDetails($slug);
}

// Hàm tính khoảng thời gian từ ngày cập nhật đến hiện tại
function timeAgo($dateString) {
    if (empty($dateString) || $dateString === null) {
        return 'N/A';
    }
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

// Hàm lấy lượt xem từ cơ sở dữ liệu
function getViews($slug) {
    global $conn;
    $query = "SELECT views FROM truyen WHERE slug = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['views'] ?? 0;
}

// Hàm lấy top truyện xem nhiều nhất từ cơ sở dữ liệu
function getMostViewedTruyen() {
    global $conn, $truyenListBySlug;
    $query = "SELECT slug, name, thumb_url, updated_at FROM truyen ORDER BY views DESC LIMIT 12";
    $result = $conn->query($query);
    $topTruyen = [];
    while ($row = $result->fetch_assoc()) {
        $details = getStoryDetails($row['slug']);
        // Ưu tiên chaptersLatest từ API /home nếu có
        $chaptersLatest = isset($truyenListBySlug[$row['slug']]['chaptersLatest']) 
            ? $truyenListBySlug[$row['slug']]['chaptersLatest'] 
            : $details['chaptersLatest'];
        // Ưu tiên updatedAt từ API /home nếu có
        $updatedAt = isset($truyenListBySlug[$row['slug']]['updatedAt']) 
            ? $truyenListBySlug[$row['slug']]['updatedAt'] 
            : $row['updated_at'];
        $topTruyen[] = [
            'slug' => $row['slug'],
            'name' => $row['name'],
            'thumb_url' => $row['thumb_url'],
            'updatedAt' => $updatedAt,
            'chaptersLatest' => $chaptersLatest,
            'category' => $details['category'],
            'views' => getViews($row['slug'])
        ];
    }
    return $topTruyen;
}

function getTopUsers() {
    global $conn;
    $query = "SELECT user_id, username, name, email, avatar, score FROM users ORDER BY score DESC LIMIT 10";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$topUsers = getTopUsers();
$mostViewedTruyen = getMostViewedTruyen();
$featuredStories = array_slice($truyenList, 0, 5);
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
    <meta property="og:image" content="https://www.truyentranhnet.com/img/logo.png">
    <meta property="og:url" content="https://www.truyentranhnet.com">
    <meta name="robots" content="index, follow">
    <link href="img/logo.png" rel="icon">
    <title>TRUYENTRANHNET - Đọc Truyện Miễn Phí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* CSS cho tag thời gian cập nhật */
        .update-tag {
            background-color: #00b7eb;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* Badge 18+ */
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

        /* CSS cho phần Top Xem Nhiều */
        .top-most-viewed {
            background: linear-gradient(135deg, #ff6b6b, #4ecdc4);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-top: 40px;
            animation: fadeIn 1s ease-in-out;
        }

        .section-title-top {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Hiệu ứng cho top 3 */
        .vip-card {
            border: 2px solid #ffd700;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .vip-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        /* Badge xếp hạng */
        .rank-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            width: 30px;
            height: 30px;
            background-color: #ffd700;
            color: #000;
            font-weight: bold;
            font-size: 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="dark-mode">
    <?php include 'includes/header.php'; ?>
    <div class="container my-4">
        <h4 class="section-title text-center">TRUYỆN TRANH MỚI CẬP NHẬT</h4>
        <div class="row g-4 fade-in">
            <?php if (!empty($truyenList)): ?>
                <?php foreach ($truyenList as $truyen): ?>
                    <div class="col-6 col-md-4 col-lg-2 mb-4">
                        <div class="manga-card">
                            <a href="views/truyen-detail.php?slug=<?= urlencode($truyen['slug']) ?>" class="text-decoration-none position-relative">
                                <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                     class="card-img-top manga-cover" 
                                     alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                     loading="lazy">
                                <?php 
                                if (isset($truyen['category']) && is_array($truyen['category'])) {
                                    foreach ($truyen['category'] as $cat) {
                                        if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                            echo '<span class="badge-18plus">18+</span>';
                                            break;
                                        }
                                    }
                                }
                                ?>
                            </a>
                            <div class="card-body p-2">
                                <h5 class="manga-title" title="<?= htmlspecialchars($truyen['name']) ?>"><?= htmlspecialchars($truyen['name']) ?></h5>
                                <div class="text-muted small d-flex justify-content-between align-items-center mt-1">
                                    <span><i class="fas fa-bookmark"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'N/A') ?></span>
                                    <span><i class="fas fa-clock"></i> <?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-eye"></i> <?= getViews($truyen['slug']) ?> lượt xem
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có dữ liệu truyện để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- Phần Top Truyện Được Xem Nhiều Nhất -->
        <div class="top-most-viewed">
            <h4 class="section-title-top">TOP TRUYỆN ĐƯỢC XEM NHIỀU NHẤT</h4>
            <div class="row g-4 fade-in">
                <?php if (!empty($mostViewedTruyen)): ?>
                    <?php foreach ($mostViewedTruyen as $index => $truyen): ?>
                        <div class="col-6 col-md-4 col-lg-2 mb-4">
                            <div class="manga-card <?= $index < 3 ? 'vip-card' : '' ?>">
                                <a href="views/truyen-detail.php?slug=<?= urlencode($truyen['slug']) ?>" class="text-decoration-none position-relative">
                                    <img src="https://img.otruyenapi.com/uploads/comics/<?= htmlspecialchars($truyen['thumb_url']) ?>" 
                                         class="card-img-top manga-cover" 
                                         alt="<?= htmlspecialchars($truyen['name']) ?>" 
                                         loading="lazy">
                                    <?php 
                                    if (isset($truyen['category']) && is_array($truyen['category'])) {
                                        foreach ($truyen['category'] as $cat) {
                                            if (in_array($cat['name'], ['Adult', '16+', 'Ecchi', 'Smut'])) {
                                                echo '<span class="badge-18plus">18+</span>';
                                                break;
                                            }
                                        }
                                    }
                                    if ($index < 3) {
                                        echo '<span class="rank-badge">' . ($index + 1) . '</span>';
                                    }
                                    ?>
                                </a>
                                <div class="card-body p-2">
                                    <h5 class="manga-title" title="<?= htmlspecialchars($truyen['name']) ?>"><?= htmlspecialchars($truyen['name']) ?></h5>
                                    <div class="text-muted small d-flex justify-content-between align-items-center mt-1">
                                        <span><i class="fas fa-bookmark"></i> <?= htmlspecialchars($truyen['chaptersLatest'][0]['chapter_name'] ?? 'N/A') ?></span>
                                        <span><i class="fas fa-clock"></i> <?= timeAgo($truyen['updatedAt'] ?? null) ?></span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-eye"></i> <?= $truyen['views'] ?> lượt xem
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">Không có truyện được xem nhiều nhất để hiển thị.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>