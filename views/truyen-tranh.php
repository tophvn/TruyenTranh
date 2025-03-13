<?php
include('../config/database.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy slug từ URL (được ánh xạ từ .htaccess)
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($slug)) {
    echo "Không tìm thấy slug.";
    exit;
}

$apiUrl = "https://otruyenapi.com/v1/api/truyen-tranh/" . urlencode($slug);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo "Không thể kết nối tới API.";
    exit;
}

$comic = json_decode($response, true);
if (!$comic || !isset($comic['data']['item'])) {
    echo "Có lỗi xảy ra, vui lòng reload lại trang!";
    exit;
}

$comicData = $comic['data']['item'];

// Lưu thông tin truyện vào cơ sở dữ liệu và tăng views, daily_views
function saveComicToDatabase($comicData) {
    global $conn;

    $updatedAt = isset($comicData['updatedAt']) ? 
        date('Y-m-d H:i:s', strtotime($comicData['updatedAt'])) : null;

    $checkQuery = "SELECT id, views, daily_views, last_view_date FROM truyen WHERE slug = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $comicData['slug']);
    $stmt->execute();
    $result = $stmt->get_result();

    $randomViews = rand(50, 300);

    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO truyen (name, slug, thumb_url, origin_name, status, updated_at, views, daily_views, last_view_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssssii", 
            $comicData['name'], 
            $comicData['slug'], 
            $comicData['thumb_url'], 
            $comicData['origin_name'][0], 
            $comicData['status'], 
            $updatedAt, 
            $randomViews, 
            $randomViews
        );
        $success = $insertStmt->execute();
        if ($success) {
            $truyenId = $conn->insert_id;
        } else {
            return false;
        }
    } else {
        $row = $result->fetch_assoc();
        $truyenId = $row['id'];
        $lastViewDate = $row['last_view_date'];

        $currentDate = date('Y-m-d');
        if ($lastViewDate !== $currentDate) {
            $updateQuery = "UPDATE truyen SET views = views + ?, daily_views = ?, last_view_date = CURDATE() WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $randomViews, $randomViews, $truyenId);
        } else {
            $updateQuery = "UPDATE truyen SET views = views + ?, daily_views = daily_views + ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $randomViews, $randomViews, $truyenId);
        }
        $updateStmt->execute();
    }

    return $truyenId;
}

$truyenId = saveComicToDatabase($comicData);
if ($truyenId === false) {
    echo "Lỗi khi lưu truyện vào cơ sở dữ liệu!";
    exit;
}

// Định dạng ngày
function formatDate($dateString) {
    if ($dateString) {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
    return 'N/A';
}

// Hàm thêm vào danh sách theo dõi
function addToFollowing($userId, $truyenId) {
    global $conn;

    if (!$userId || !$truyenId) {
        return ['success' => false, 'message' => 'Thông tin không hợp lệ'];
    }

    $checkQuery = "SELECT id FROM yeuthich WHERE user_id = ? AND truyen_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $userId, $truyenId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO yeuthich (user_id, truyen_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ii", $userId, $truyenId);
        $success = $insertStmt->execute();
        if ($success) {
            return ['success' => true, 'message' => 'Đã thêm vào danh sách theo dõi!'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi thêm: ' . $conn->error];
        }
    }
    return ['success' => false, 'message' => 'Truyện đã có trong danh sách theo dõi!'];
}

// Hàm thêm lượt thích
function addToLikes($userId, $truyenId) {
    global $conn;

    if (!$userId || !$truyenId) {
        return ['success' => false, 'message' => 'Thông tin không hợp lệ'];
    }

    $checkQuery = "SELECT id FROM likes WHERE user_id = ? AND truyen_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $userId, $truyenId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO likes (user_id, truyen_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ii", $userId, $truyenId);
        $success = $insertStmt->execute();

        if ($success) {
            $updateQuery = "UPDATE truyen SET likes = likes + 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $truyenId);
            $updateStmt->execute();
            return ['success' => true, 'message' => 'Đã thích truyện!'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi thích: ' . $conn->error];
        }
    }
    return ['success' => false, 'message' => 'Bạn đã thích truyện này rồi!'];
}

// Xử lý thêm vào danh sách theo dõi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_following'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào danh sách theo dõi!']);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
        exit;
    }
    $userId = $_SESSION['user']['user_id'];
    $result = addToFollowing($userId, $truyenId);
    echo json_encode($result);
    exit;
}

// Xử lý thêm lượt thích
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_likes'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thích truyện!']);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
        exit;
    }
    $userId = $_SESSION['user']['user_id'];
    $result = addToLikes($userId, $truyenId);
    echo json_encode($result);
    exit;
}

// Xử lý gửi bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận!']);
        exit;
    }
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);
        exit;
    }

    $userId = $_SESSION['user']['user_id'];
    $content = trim($_POST['comment_content']);

    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống!']);
        exit;
    }
    if (strlen($content) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Bình luận quá dài (tối đa 1000 ký tự)!']);
        exit;
    }

    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    $insertQuery = "INSERT INTO comments (user_id, truyen_id, content, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iis", $userId, $truyenId, $content);
    $success = $stmt->execute();

    if ($success) {
        $userQuery = "SELECT name, avatar FROM users WHERE user_id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();

        $avatar = $user['avatar'] ? $user['avatar'] : '../img/default-avatar.png';

        echo json_encode([
            'success' => true,
            'message' => 'Bình luận đã được gửi!',
            'comment' => [
                'name' => $user['name'],
                'content' => $content,
                'avatar' => $avatar,
                'created_at' => date('d/m/Y H:i')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi bình luận: ' . $conn->error]);
    }
    exit;
}

// Lấy danh sách bình luận
function getComments($truyenId) {
    global $conn;
    $query = "SELECT u.name, u.avatar, c.content, c.created_at 
              FROM comments c 
              JOIN users u ON c.user_id = u.user_id 
              WHERE c.truyen_id = ? 
              ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $truyenId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Lấy thông tin truyện từ database
function getComicStats($truyenId) {
    global $conn;
    $statsQuery = "SELECT views, likes, daily_views FROM truyen WHERE id = ?";
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param("i", $truyenId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();

    $followQuery = "SELECT COUNT(*) as follows FROM yeuthich WHERE truyen_id = ?";
    $followStmt = $conn->prepare($followQuery);
    $followStmt->bind_param("i", $truyenId);
    $followStmt->execute();
    $followResult = $followStmt->get_result();
    $follows = $followResult->fetch_assoc()['follows'];

    return [
        'views' => $stats['views'],
        'likes' => $stats['likes'],
        'daily_views' => $stats['daily_views'],
        'follows' => $follows
    ];
}

$comments = getComments($truyenId);
$comicStats = getComicStats($truyenId);

// Đề xuất
$homeApiUrl = "https://otruyenapi.com/v1/api/home";
$chHome = curl_init();
curl_setopt($chHome, CURLOPT_URL, $homeApiUrl);
curl_setopt($chHome, CURLOPT_RETURNTRANSFER, true);
$responseHome = curl_exec($chHome);
curl_close($chHome);

$homeData = json_decode($responseHome, true);
$recommendedStories = [];
if ($homeData && isset($homeData['data']['items'])) {
    $recommendedStories = $homeData['data']['items'];
    shuffle($recommendedStories);
    $recommendedStories = array_slice($recommendedStories, 0, 6);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title><?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .chapter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sort-btn {
            padding: 5px 10px;
            cursor: pointer;
            background-color: #1f2937;
            color: #00b7eb;
            border: 1px solid #374151;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .sort-btn:hover {
            background-color: #374151;
            color: #fff;
        }

        .follow-button, .like-button {
            background-color: #ff6200;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }

        .like-button {
            background-color: #ff4444;
        }

        .follow-button:hover {
            background-color: #e65c00;
        }

        .like-button:hover {
            background-color: #cc0000;
        }

        .follow-button i, .like-button i {
            margin-right: 5px;
        }

        .age-warning {
            background-color: #4b1b17;
            color: #ffcccc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .badge-18plus {
            position: absolute;
            top: 8px;
            right: 8px;
            background-color: #ff0000;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            z-index: 10;
        }

        .chapters {
            max-height: 400px;
            overflow-y: auto;
        }

        .recommended-card {
            background: #2d3748;
            border-radius: 10px;
            padding: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .recommended-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }

        .recommended-card img {
            width: 96px; 
            height: 144px; 
            object-fit: cover;
            border-radius: 5px;
        }

        .recommended-card h5 {
            color: #ffffff;
            font-size: 0.9rem;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .recommended-card p {
            color: #a0aec0;
            font-size: 0.75rem;
            line-height: 1.2;
        }
    </style>
</head>
<body class="bg-gray-900 text-white font-poppins">
    <?php include '../includes/header.php' ?>
    
    <main class="container mx-auto px-4 py-8 pt-16">
        <div class="manga-detail flex flex-col lg:flex-row gap-8">
            <div class="left-column lg:w-3/4">
                <div class="manga-info bg-gray-800 p-6 rounded-lg shadow-md border border-gray-700">
                    <div class="flex flex-col lg:flex-row items-center gap-6">
                        <img src="https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comicData['thumb_url'] ?? ''); ?>" 
                            class="w-40 h-60 object-cover rounded-lg" 
                            alt="<?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?>">
                        <div class="flex-1">
                            <h1 class="text-2xl font-semibold mb-2 text-white"><?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?></h1>
                            <p class="text-gray-300 mb-1"><br><strong>Tên gốc:</strong> <?php echo htmlspecialchars($comicData['origin_name'][0] ?? 'N/A'); ?></p>
                            <p class="text-gray-300 mb-1"><strong>Tác giả:</strong> Đang update</p>
                            <p class="text-gray-300 mb-1"><strong>Cập nhật lần cuối:</strong> <?php echo htmlspecialchars(formatDate($comicData['updatedAt'] ?? '')); ?></p>
                            <p class="text-gray-300 mb-1"><strong>Lượt thích:</strong> <?php echo $comicStats['likes']; ?></p>
                            <p class="text-gray-300 mb-1"><strong>Lượt theo dõi:</strong> <?php echo $comicStats['follows']; ?></p>
                            <p class="text-gray-300 mb-1"><strong>Lượt xem:</strong> <?php echo $comicStats['views']; ?></p>
                            <p class="text-gray-300 mb-1">
                                <strong>Trạng thái:</strong> 
                                <?php 
                                $status = $comicData['status'] ?? 'unknown';
                                if ($status === 'ongoing') {
                                    echo '<span class="text-green-500">Đang tiến hành</span>';
                                } elseif ($status === 'completed') {
                                    echo '<span class="text-blue-500">Đã hoàn thành</span>';
                                } else {
                                    echo '<span class="text-gray-500">Không rõ</span>';
                                }
                                ?>
                            </p>
                            <div class="rating flex items-center mt-2">
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <i class="fas fa-star text-yellow-400"></i>
                                <span class="ml-2 text-yellow-400">5/5</span>
                            </div>
                            <div class="tags flex flex-wrap gap-2 mt-2">
                                <?php 
                                if (isset($comicData['category']) && is_array($comicData['category'])) {
                                    foreach ($comicData['category'] as $cat) {
                                        $slug = isset($cat['slug']) ? $cat['slug'] : strtolower(str_replace(' ', '-', $cat['name']));
                                        echo '<a href="../views/truyen-theo-the-loai/' . htmlspecialchars($slug) . '" 
                                            class="bg-gray-700 text-gray-200 px-2 py-1 rounded text-sm hover:bg-gray-600 transition-colors duration-300 no-underline">
                                            ' . htmlspecialchars($cat['name']) . '</a>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="mt-4">
                                <button class="follow-button" title="Thêm vào danh sách theo dõi" id="addToFollowing">
                                    <i class="fas fa-bookmark"></i> Theo dõi
                                </button>
                                <button class="like-button" title="Thích truyện" id="addToLikes">
                                    <i class="fas fa-heart"></i> Thích
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $showAgeWarning = false;
                $sensitiveTags = ['Adult', '16+', 'Ecchi', 'Smut', '18+'];
                if (isset($comicData['category']) && is_array($comicData['category'])) {
                    foreach ($comicData['category'] as $cat) {
                        if (in_array(strtoupper($cat['name']), array_map('strtoupper', $sensitiveTags))) {
                            $showAgeWarning = true;
                            break;
                        }
                    }
                }
                if ($showAgeWarning): ?>
                    <div class="age-warning mt-4">
                        Cảnh báo độ tuổi: Truyện tranh <?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?> có thể có nội dung và hình ảnh không phù hợp với lứa tuổi của bạn. Nếu bạn dưới 16 tuổi, vui lòng chọn một truyện khác để giải trí. Chúng tôi sẽ không chịu trách nhiệm liên quan nếu bạn bỏ qua cảnh báo này.
                    </div>
                <?php endif; ?>

                <div class="description bg-gray-800 p-6 rounded-lg shadow-md mt-4 border border-gray-700">
                    <hr class="border-gray-600">
                    <h2 class="text-xl font-semibold mb-2 text-white">Mô Tả</h2>
                    <p class="text-gray-300"><?php echo htmlspecialchars(strip_tags($comicData['content'] ?? '')); ?></p>
                </div>

                <div class="chapter-list bg-gray-800 p-6 rounded-lg shadow-md mt-4 border border-gray-700">
                    <div class="chapter-header mb-4">
                        <h2 class="section-title text-xl font-semibold text-white">
                            <i class="fas fa-star text-yellow-400"></i>
                            DANH SÁCH CHƯƠNG
                        </h2>
                        <button id="toggleSort" class="sort-btn" title="Sắp xếp">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                    </div>
                    <div class="mb-4 text-center">
                        <div class="flex justify-center gap-3">
                            <button id="readFirstChapter" class="btn bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded">Đọc Từ Đầu</button>
                            <button id="readLatestChapter" class="btn bg-gray-600 text-white hover:bg-gray-700 px-4 py-2 rounded">Đọc từ Chapter Mới Nhất</button>
                        </div>
                    </div>
                    <div class="chapters" id="chapterContainer" style="max-height: 400px; overflow-y: auto;">
                    <?php
                    $chapters = [];
                    if (isset($comicData['chapters']) && is_array($comicData['chapters'])) {
                        foreach ($comicData['chapters'] as $chapter) {
                            foreach ($chapter['server_data'] as $data) {
                                $chapterUrl = isset($data['chapter_api_data']) ? htmlspecialchars($data['chapter_api_data']) : '#';
                                $chapterName = isset($data['chapter_name']) ? htmlspecialchars($data['chapter_name']) : 'Chương mới';
                                $chapters[] = [
                                    'name' => $chapterName,
                                    'url' => $chapterUrl
                                ];
                            }
                        }
                    } else {
                        echo "<p class='text-gray-400'>Không có chương nào để hiển thị.</p>";
                    }

                    // Debug dữ liệu chapters
                    echo "<script>console.log('Chapters:', " . json_encode($chapters) . ");</script>";

                    foreach ($chapters as $index => $chapter) {
                        $encodedChapterUrl = base64_encode($chapter['url']);
                        echo '<div class="chapter-item bg-gray-700 p-3 rounded mb-2 hover:bg-gray-600 transition cursor-pointer" data-chapter-url="' . htmlspecialchars($encodedChapterUrl) . '">
                                <span class="chapter-name text-white">Chương ' . $chapter['name'] . '</span>
                                <span class="chapter-details text-sm text-gray-400"> 0 lượt xem - 0 bình luận</span>
                            </div>';
                    }
                    ?>
                </div>
                </div>

                <div class="comment-section bg-gray-800 p-6 rounded-lg shadow-md mt-4 border border-gray-700">
                    <h3 class="text-xl font-semibold mb-4 text-white">Bình Luận</h3>
                    <?php if (isset($_SESSION['user']['user_id'])): ?>
                        <form id="commentForm" class="comment-form" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <textarea name="comment_content" placeholder="Viết bình luận của bạn..." required class="w-full p-3 bg-gray-700 text-white rounded mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            <button type="submit" class="btn bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded w-full">Gửi Bình Luận</button>
                        </form>
                    <?php else: ?>
                        <p class="text-center text-gray-400">Vui lòng <a href="../login.php" class="text-blue-400 hover:underline">đăng nhập</a> để bình luận!</p>
                    <?php endif; ?>

                    <div class="comment-list mt-4">
                        <?php
                        if (is_array($comments) && !empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item flex items-start mb-4">
                                    <img src="<?php echo htmlspecialchars($comment['avatar'] ?? '../img/default-avatar.png'); ?>" alt="Avatar" class="w-10 h-10 rounded-full mr-2">
                                    <div class="comment-details">
                                        <div class="comment-header flex justify-between items-center">
                                            <span class="comment-username text-white font-medium"><?php echo htmlspecialchars($comment['name'] ?? 'Unknown'); ?> </span>
                                            <span class="comment-time text-sm text-gray-400"><?php echo formatDate($comment['created_at']); ?> </span>
                                        </div>
                                        <span class="comment-content text-gray-300 mt-1"> <?php echo htmlspecialchars($comment['content'] ?? ''); ?> </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-gray-400">Chưa có bình luận nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="right-column lg:w-1/4">
                <div class="recommended-stories bg-gray-800 p-4 rounded-lg shadow-md border border-gray-700">
                    <h3 class="text-xl font-semibold mb-4 text-white">ĐỀ XUẤT</h3>
                    <div class="space-y-4">
                        <?php
                        if (!empty($recommendedStories)) {
                            foreach ($recommendedStories as $story) {
                                $chapterInfo = isset($story['chaptersLatest'][0]['chapter_name']) ? 
                                    'Ch. ' . $story['chaptersLatest'][0]['chapter_name'] : 'Ch. N/A';
                                echo '<div class="recommended-card flex items-start gap-3">
                                        <a href="../truyen-tranh/' . htmlspecialchars($story['slug']) . '" class="block flex items-start gap-3">
                                            <img src="https://img.otruyenapi.com/uploads/comics/' . htmlspecialchars($story['thumb_url']) . '" 
                                                alt="' . htmlspecialchars($story['name']) . '" 
                                                class="w-24 h-36 object-cover rounded-lg">
                                            <div class="flex-1">
                                                <h5 class="text-white font-semibold text-sm break-words">' . htmlspecialchars($story['name']) . '</h5>
                                                <p class="text-gray-400 text-xs">' . $chapterInfo . '</p>
                                            </div>
                                        </a>
                                    </div>';
                            }
                        } else {
                            echo '<p class="text-center text-gray-400">Không có truyện đề xuất.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div><br>
    </main>

    <?php include '../includes/footer.php' ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    let chapters = <?php echo json_encode($chapters); ?>;
    let isSortDesc = true;

    function renderChapters() {
        let sortedChapters = [...chapters];
        if (isSortDesc) {
            sortedChapters.sort((a, b) => parseFloat(b.name) - parseFloat(a.name));
        } else {
            sortedChapters.sort((a, b) => parseFloat(a.name) - parseFloat(b.name));
        }

        const container = $('#chapterContainer');
        container.empty();
        if (sortedChapters.length === 0) {
            container.append('<p class="text-gray-400">Không có chương nào để hiển thị.</p>');
            return;
        }

        sortedChapters.forEach(chapter => {
            const encodedChapterUrl = btoa(chapter.url);
            const chapterHtml = `
                <div class="chapter-item bg-gray-700 p-3 rounded mb-2 hover:bg-gray-600 transition cursor-pointer" data-chapter-url="${encodedChapterUrl}">
                    <span class="chapter-name text-white">Chương ${chapter.name}</span>
                    <span class="chapter-details text-sm text-gray-400"> - 0 lượt xem - 0 bình luận</span>
                </div>`;
            container.append(chapterHtml);
        });
    }

    // Hàm lưu lịch sử đọc
    function saveToReadHistory(chapterUrl, storySlug, chapterName) {
        let readHistory = JSON.parse(localStorage.getItem('readHistory')) || [];
        
        const chapterInfo = {
            filename: `${storySlug}-${chapterName}`,
            chapter_link: `/TruyenTranhNet/views/doc-truyen.php?chapter_url=${encodeURIComponent(chapterUrl)}&story_slug=${encodeURIComponent(storySlug)}`,
            chapter_story_name: "<?php echo htmlspecialchars($comicData['name']); ?>",
            chapter_name: `Chương ${chapterName}`,
            chapter_image: `https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comicData['thumb_url']); ?>`,
            timestamp: new Date().toISOString()
        };

        const existingIndex = readHistory.findIndex(item => item.filename === chapterInfo.filename);
        if (existingIndex !== -1) {
            readHistory[existingIndex] = chapterInfo;
        } else {
            readHistory.push(chapterInfo);
        }

        if (readHistory.length > 50) {
            readHistory.shift();
        }

        localStorage.setItem('readHistory', JSON.stringify(readHistory));
    }

    $(document).ready(function() {
        renderChapters();

        $('#commentForm').on('submit', function(e) {
            e.preventDefault();
            let content = $('textarea[name="comment_content"]').val();
            let csrfToken = $('input[name="csrf_token"]').val();

            $.post({
                url: '',
                data: { comment_content: content, csrf_token: csrfToken },
                success: function(data) {
                    const response = JSON.parse(data);
                    alert(response.message);
                    if (response.success) {
                        let newComment = `
                            <div class="comment-item flex items-start mb-4">
                                <img src="${response.comment.avatar}" alt="Avatar" class="w-10 h-10 rounded-full mr-2">
                                <div class="comment-details">
                                    <div class="comment-header flex justify-between items-center">
                                        <span class="comment-username text-white font-medium">${response.comment.name}</span>
                                        <span class="comment-time text-sm text-gray-400">${response.comment.created_at}</span>
                                    </div>
                                    <span class="comment-content text-gray-300 mt-1">${response.comment.content}</span>
                                </div>
                            </div>`;
                        $('.comment-list').prepend(newComment);
                        $('textarea[name="comment_content"]').val('');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi gửi bình luận.');
                }
            });
        });

        $('#chapterContainer').on('click', '.chapter-item', function() {
            let encodedChapterUrl = $(this).data('chapter-url');
            let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
            let chapterName = $(this).find('.chapter-name').text().replace('Chương ', '').trim();
            
            saveToReadHistory(encodedChapterUrl, storySlug, chapterName);
            window.location.href = `/TruyenTranhNet/views/doc-truyen.php?chapter_url=${encodedChapterUrl}&story_slug=${encodeURIComponent(storySlug)}`;
        });

        $('#readFirstChapter').on('click', function() {
            let chapterUrl = btoa(chapters[0].url);
            let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
            let chapterName = chapters[0].name;
            
            saveToReadHistory(chapterUrl, storySlug, chapterName);
            window.location.href = `/TruyenTranhNet/views/doc-truyen.php?chapter_url=${chapterUrl}&story_slug=${encodeURIComponent(storySlug)}`;
        });

        $('#readLatestChapter').on('click', function() {
            let chapterUrl = btoa(chapters[chapters.length - 1].url);
            let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
            let chapterName = chapters[chapters.length - 1].name;
            
            saveToReadHistory(chapterUrl, storySlug, chapterName);
            window.location.href = `/TruyenTranhNet/views/doc-truyen.php?chapter_url=${chapterUrl}&story_slug=${encodeURIComponent(storySlug)}`;
        });

        $('#addToFollowing').on('click', function() {
            let csrfToken = $('input[name="csrf_token"]').val();
            $.post({
                url: '',
                data: { add_to_following: true, csrf_token: csrfToken },
                success: function(data) {
                    const response = JSON.parse(data);
                    alert(response.message);
                    if (response.success) {
                        let currentFollows = parseInt($('p:contains("Lượt theo dõi")').text().match(/\d+/)[0]);
                        $('p:contains("Lượt theo dõi")').html(`<strong>Lượt theo dõi:</strong> ${currentFollows + 1}`);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi thêm vào danh sách theo dõi.');
                }
            });
        });

        $('#addToLikes').on('click', function() {
            let csrfToken = $('input[name="csrf_token"]').val();
            $.post({
                url: '',
                data: { add_to_likes: true, csrf_token: csrfToken },
                success: function(data) {
                    const response = JSON.parse(data);
                    alert(response.message);
                    if (response.success) {
                        let currentLikes = parseInt($('p:contains("Lượt thích")').text().match(/\d+/)[0]);
                        $('p:contains("Lượt thích")').html(`<strong>Lượt thích:</strong> ${currentLikes + 1}`);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi thích truyện.');
                }
            });
        });

        $('#toggleSort').on('click', function() {
            isSortDesc = !isSortDesc;
            const icon = $(this).find('i');
            if (isSortDesc) {
                icon.removeClass('fa-sort-amount-up').addClass('fa-sort-amount-down');
            } else {
                icon.removeClass('fa-sort-amount-down').addClass('fa-sort-amount-up');
            }
            renderChapters();
        });
    });
</script>
</body>
</html>