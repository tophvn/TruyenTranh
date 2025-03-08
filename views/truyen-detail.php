<?php
include('../config/database.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy dữ liệu từ API cho truyện chính
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
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

// Lưu thông tin truyện vào cơ sở dữ liệu và lấy ID
function saveComicToDatabase($comicData) {
    global $conn;

    $updatedAt = isset($comicData['updatedAt']) ? 
        date('Y-m-d H:i:s', strtotime($comicData['updatedAt'])) : null;

    $checkQuery = "SELECT id, views, likes FROM truyen WHERE slug = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $comicData['slug']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Nếu truyện chưa tồn tại, thêm mới với views ban đầu là 0
        $insertQuery = "INSERT INTO truyen (name, slug, thumb_url, origin_name, status, updated_at, views, likes) VALUES (?, ?, ?, ?, ?, ?, 0, 0)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ssssss", $comicData['name'], $comicData['slug'], $comicData['thumb_url'], $comicData['origin_name'][0], $comicData['status'], $updatedAt);
        $success = $insertStmt->execute();
        if ($success) {
            $truyenId = $conn->insert_id;
        } else {
            return false;
        }
    } else {
        // Nếu truyện đã tồn tại, lấy ID
        $row = $result->fetch_assoc();
        $truyenId = $row['id'];
    }

    // Tăng ngẫu nhiên views từ 10 đến 100
    $randomViews = rand(10, 100);
    $updateQuery = "UPDATE truyen SET views = views + ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $randomViews, $truyenId);
    $updateStmt->execute();

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
    $statsQuery = "SELECT views, likes FROM truyen WHERE id = ?";
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
    <title>Chi Tiết Truyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/truyen-detail.css">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .chapter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sort-btn {
            padding: 5px 10px;
            cursor: pointer;
        }
        
        .follow-button, .like-button {
            background-color: transparent;
            color: #ff6200;
            border: 2px solid #ff6200;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }

        .follow-button {
            background-color: #ff6200;
            color: white;
        }

        .like-button {
            background-color: #ff4444;
            color: white;
            border-color: #ff4444;
        }

        .follow-button:hover {
            background-color: #e65c00;
            color: white;
        }

        .like-button:hover {
            background-color: #cc0000;
            color: white;
        }

        .follow-button i, .like-button i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php' ?>
    
    <main class="container">
        <div class="manga-detail">
            <div class="left-column">
                <div class="manga-info">
                    <img src="https://img.otruyenapi.com/uploads/comics/<?php echo htmlspecialchars($comicData['thumb_url'] ?? ''); ?>" class="manga-cover" alt="<?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?>">
                    <div class="manga-stats">
                        <h1><?php echo htmlspecialchars($comicData['name'] ?? 'N/A'); ?></h1>
                        <p><strong>Tên gốc:</strong> <?php echo htmlspecialchars($comicData['origin_name'][0] ?? 'N/A'); ?></p>
                        <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($comicData['status'] ?? 'N/A'); ?></p>
                        <p><strong>Cập nhật lần cuối:</strong> <?php echo htmlspecialchars(formatDate($comicData['updatedAt'] ?? '')); ?></p>
                        <p><strong>Lượt thích:</strong> <?php echo $comicStats['likes']; ?></p>
                        <p><strong>Lượt theo dõi:</strong> <?php echo $comicStats['follows']; ?></p>
                        <p><strong>Lượt xem:</strong> <?php echo $comicStats['views']; ?></p>
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>5/5</span>
                        </div>
                        <div class="tags">
                            <?php 
                            if (isset($comicData['category']) && is_array($comicData['category'])) {
                                foreach ($comicData['category'] as $cat) {
                                    echo '<span class="tag">' . htmlspecialchars($cat['name']) . '</span>';
                                }
                            }
                            ?>
                        </div><br>
                        <button class="follow-button" title="Thêm vào danh sách theo dõi" id="addToFollowing">
                            <i class="fas fa-bookmark"></i> Theo dõi
                        </button>
                        <button class="like-button" title="Thích truyện" id="addToLikes">
                            <i class="fas fa-heart"></i> Thích
                        </button>
                    </div>
                </div>
                
                <div class="description mt-3">
                    <hr>
                    <h2>Mô Tả</h2>
                    <p><?php echo htmlspecialchars(strip_tags($comicData['content'] ?? '')); ?></p>
                </div>

                <div class="chapter-list">
                    <div class="chapter-header">
                        <h2 class="section-title">
                            <i class="fas fa-star"></i>
                            DANH SÁCH CHƯƠNG
                        </h2>
                        <button id="toggleSort" class="sort-btn btn btn-outline-primary" title="Sắp xếp">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                    </div>
                    <div class="mb-3 text-center">
                        <div class="d-flex justify-content-center gap-3">
                            <button id="readFirstChapter" class="btn btn-primary w-100 w-md-auto">Đọc Từ Đầu</button>
                            <button id="readLatestChapter" class="btn btn-secondary w-100 w-md-auto">Đọc từ Chapter Mới Nhất</button>
                        </div>
                    </div>
                    <div class="chapters" id="chapterContainer">
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
                            echo "<p>Không có chương nào để hiển thị.</p>";
                        }

                        foreach ($chapters as $index => $chapter) {
                            $encodedChapterUrl = base64_encode($chapter['url']);
                            echo '<div class="chapter-item" data-chapter-url="' . htmlspecialchars($encodedChapterUrl) . '">
                                    <span class="chapter-name">Chương ' . $chapter['name'] . '</span>
                                    <span class="chapter-details">Đang update - 0 lượt xem - 0 bình luận</span>
                                  </div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="comment-section">
                    <h3>Bình Luận</h3>
                    <?php if (isset($_SESSION['user']['user_id'])): ?>
                        <form id="commentForm" class="comment-form" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <textarea name="comment_content" placeholder="Viết bình luận của bạn..." required></textarea>
                            <button type="submit">Gửi Bình Luận</button>
                        </form>
                    <?php else: ?>
                        <p>Vui lòng <a href="../login.php">đăng nhập</a> để bình luận!</p>
                    <?php endif; ?>

                    <div class="comment-list">
                        <?php
                        if (is_array($comments) && !empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <img src="<?php echo htmlspecialchars($comment['avatar'] ?? '../img/default-avatar.png'); ?>" alt="Avatar" class="comment-avatar">
                                    <div class="comment-details">
                                        <div class="comment-header">
                                            <span class="comment-username"><?php echo htmlspecialchars($comment['name'] ?? 'Unknown'); ?></span>
                                            <span class="comment-time"><?php echo formatDate($comment['created_at']); ?></span>
                                        </div>
                                        <span class="comment-content"><?php echo htmlspecialchars($comment['content'] ?? ''); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Chưa có bình luận nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="recommended-stories">
                    <h3>ĐỀ XUẤT</h3>
                    <?php
                    if (!empty($recommendedStories)) {
                        foreach ($recommendedStories as $story) {
                            $chapterInfo = isset($story['chapter']) && isset($story['chapter']['chapter_name']) ? 
                                'Ch. ' . $story['chapter']['chapter_name'] : 'Ch. N/A';
                            echo '<div class="recommended-item">
                                    <a href="truyen-detail.php?slug=' . htmlspecialchars($story['slug']) . '">
                                        <img src="https://img.otruyenapi.com/uploads/comics/' . htmlspecialchars($story['thumb_url']) . '" alt="' . htmlspecialchars($story['name']) . '" class="recommended-image">
                                        <div class="comment-details">
                                            <span class="recommended-title">' . htmlspecialchars($story['name']) . '</span>
                                            <span class="recommended-chapter">' . $chapterInfo . '</span>
                                        </div>
                                    </a>
                                  </div>';
                        }
                    } else {
                        echo '<p>Không có truyện đề xuất.</p>';
                    }
                    ?>
                </div>
            </div>
        </div><br>
    </main>

    <?php include '../includes/footer.php' ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        let chapters = <?php echo json_encode($chapters); ?>;
        let isSortDesc = true; // Mặc định từ mới đến cũ

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
                container.append('<p>Không có chương nào để hiển thị.</p>');
                return;
            }

            sortedChapters.forEach(chapter => {
                const encodedChapterUrl = btoa(chapter.url);
                const chapterHtml = `
                    <div class="chapter-item" data-chapter-url="${encodedChapterUrl}">
                        <span class="chapter-name">Chương ${chapter.name}</span>
                        <span class="chapter-details">Đang update - 0 lượt xem - 0 bình luận</span>
                    </div>`;
                container.append(chapterHtml);
            });
        }

        $(document).ready(function() {
            // Khởi tạo danh sách chương
            renderChapters();

            // Xử lý gửi bình luận
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
                                <div class="comment-item">
                                    <img src="${response.comment.avatar}" alt="Avatar" class="comment-avatar">
                                    <div class="comment-details">
                                        <div class="comment-header">
                                            <span class="comment-username">${response.comment.name}</span>
                                            <span class="comment-time">${response.comment.created_at}</span>
                                        </div>
                                        <span class="comment-content">${response.comment.content}</span>
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

            // Xử lý click vào chapter
            $('#chapterContainer').on('click', '.chapter-item', function() {
                let encodedChapterUrl = $(this).data('chapter-url');
                let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
                window.location.href = `doc-truyen.php?chapter_url=${encodeURIComponent(encodedChapterUrl)}&story_slug=${encodeURIComponent(storySlug)}`;
            });

            // Đọc từ đầu
            $('#readFirstChapter').on('click', function() {
                let chapterUrl = btoa(chapters[0].url);
                let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
                window.location.href = `doc-truyen.php?chapter_url=${encodeURIComponent(chapterUrl)}&story_slug=${encodeURIComponent(storySlug)}`;
            });

            // Đọc chapter mới nhất
            $('#readLatestChapter').on('click', function() {
                let chapterUrl = btoa(chapters[chapters.length - 1].url);
                let storySlug = "<?php echo htmlspecialchars($comicData['slug']); ?>";
                window.location.href = `doc-truyen.php?chapter_url=${encodeURIComponent(chapterUrl)}&story_slug=${encodeURIComponent(storySlug)}`;
            });

            // Thêm vào danh sách theo dõi
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

            // Thêm lượt thích
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

            // Toggle sort
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