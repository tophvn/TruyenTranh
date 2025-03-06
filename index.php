<?php
include('config/database.php');

session_start();
$api_url = "https://otruyenapi.com/v1/api/home";
$isIndexPage = basename($_SERVER['PHP_SELF']) === 'index.php';

// Gọi API home để lấy danh sách truyện
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$truyenList = (isset($data['data']) && !empty($data['data'])) ? $data['data']['items'] : [];

// Hàm lấy thông tin truyện từ API theo slug (chỉ lấy tên truyện)
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
            'name' => $data['data']['item']['name']
        ];
    }
    return ['name' => 'Không tìm thấy'];
}

// Danh sách slug của các truyện bạn muốn hiển thị trong carousel
$storySlugs = ['cau-lac-bo-sieu-cap-ve-nha', 'chainsawman-phan-2', 'ruri-dragon', 'dai-chien-nguoi-khong-lo'];
$stories = [];
foreach ($storySlugs as $slug) {
    $stories[$slug] = getStoryDetails($slug);
}

// Hàm định dạng thời gian từ API
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

// Hàm lấy top truyện xem nhiều nhất từ API, sắp xếp theo views từ CSDL
function getMostViewedTruyen($truyenList) {
    foreach ($truyenList as &$truyen) {
        $truyen['views'] = getViews($truyen['slug']);
    }
    unset($truyen);
    usort($truyenList, function($a, $b) {
        return ($b['views'] ?? 0) - ($a['views'] ?? 0);
    });
    return array_slice($truyenList, 0, 12);
}

function getTopUsers() {
    global $conn;
    $query = "SELECT user_id, username, name, email, avatar, score FROM users ORDER BY score DESC LIMIT 10";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$topUsers = getTopUsers();
$mostViewedTruyen = getMostViewedTruyen($truyenList);
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
        .premium-carousel {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .premium-carousel .carousel-inner {
            border-radius: 15px;
        }

        .premium-carousel .carousel-item {
            height: 500px;
            background: linear-gradient(135deg, #1e3a8a, #4c6ef5);
            transition: transform 0.6s ease, opacity 0.6s ease;
        }

        .premium-carousel .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .premium-carousel .carousel-item:hover img {
            opacity: 1;
        }

        .premium-carousel .carousel-caption {
            text-align: center;
            bottom: 20px;
            top: auto;
            transform: none;
            padding: 20px;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            width: 90%;
            left: 5%;
            right: 5%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .premium-carousel .carousel-caption h5 {
            font-size: 2rem;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 10px;
            font-style: italic;
        }

        .premium-carousel .btn-xem-thong-tin {
            background: #3b82f6;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-block;
        }

        .premium-carousel .btn-xem-thong-tin:hover {
            background: #2563eb;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        @media (max-width: 768px) {
            .premium-carousel .carousel-item {
                height: 300px;
            }

            .premium-carousel .carousel-caption h5 {
                font-size: 1.5rem;
            }

            .premium-carousel .carousel-caption {
                width: 90%;
                padding: 15px;
            }
        }
    </style>
</head>
<body><br>
    <?php include 'includes/header.php'; ?>

    <div class="container my-4">
        <?php if ($isIndexPage): ?>
            <div class="row mb-5 fade-in">
                <div class="col-12">
                    <div id="premiumCarousel" class="carousel slide premium-carousel" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($storySlugs as $index => $slug): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="img/slide/image<?= $index + 1 ?>.jpg" class="d-block w-100" alt="<?= htmlspecialchars($stories[$slug]['name']) ?>">
                                    <div class="carousel-caption">
                                        <h5><?= htmlspecialchars($stories[$slug]['name']) ?></h5>
                                        <a href="views/truyen-detail.php?slug=<?= urlencode($slug) ?>" class="btn btn-xem-thong-tin">Xem thông tin</a>
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
            </div>
        <?php endif; ?>

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
                                // Kiểm tra thể loại để hiển thị tag "18+"
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
                                    <span><i class="fas fa-clock"></i> <?= formatDate($truyen['updatedAt'] ?? null) ?></span>
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

        <h4 class="section-title text-center mt-5">TOP TRUYỆN ĐƯỢC XEM NHIỀU NHẤT</h4>
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
                                // Kiểm tra thể loại để hiển thị tag "18+"
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
                                    <span><i class="fas fa-clock"></i> <?= formatDate($truyen['updatedAt'] ?? null) ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-eye"></i> <?= getViews($truyen['slug']) ?> lượt xem
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có truyện được xem nhiều nhất để hiển thị.</p>
            <?php endif; ?>
        </div>

        <!-- <h4 class="section-title text-center mt-5">TOP THÀNH VIÊN</h4>
        <div class="row g-4 fade-in">
            <?php if (!empty($topUsers)): ?>
                <?php foreach ($topUsers as $user): ?>
                    <div class="col-6 col-md-4 col-lg-2 mb-4">
                        <div class="manga-card user-card">
                            <div class="card-body text-center p-2">
                                <img src="<?= htmlspecialchars($user['avatar'] ?? 'default-avatar.png') ?>" 
                                     alt="<?= htmlspecialchars($user['name']) ?>" 
                                     loading="lazy">
                                <h5 class="user-name mt-2" title="<?= htmlspecialchars($user['name'] ?? $user['username']) ?>"><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h5>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-trophy"></i> Điểm: <?= number_format($user['score']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Không có người dùng nào để hiển thị.</p>
            <?php endif; ?> -->
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>