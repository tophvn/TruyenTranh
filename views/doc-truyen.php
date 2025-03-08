<?php
include('../config/database.php');
session_start();

// Lấy chapter_url và story_slug từ URL
$encodedChapterUrl = isset($_GET['chapter_url']) ? urldecode($_GET['chapter_url']) : '';
$storySlug = isset($_GET['story_slug']) ? urldecode($_GET['story_slug']) : '';

if (empty($encodedChapterUrl) || empty($storySlug)) {
    echo "Không tìm thấy chương hoặc truyện.";
    exit;
}

// Giải mã chapter_url từ base64
$chapterUrl = base64_decode($encodedChapterUrl);

// Gọi API để lấy nội dung chapter
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chapterUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo "Không thể kết nối tới API.";
    exit;
}

$chapterData = json_decode($response, true);
if (!$chapterData || !isset($chapterData['data']['item'])) {
    echo "Không thể tải dữ liệu chương.";
    exit;
}

// Lấy thông tin truyện từ API
$apiUrl = "https://otruyenapi.com/v1/api/truyen-tranh/" . urlencode($storySlug);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$storyResponse = curl_exec($ch);
curl_close($ch);

$storyData = json_decode($storyResponse, true);
if (!$storyData || !isset($storyData['data']['item'])) {
    echo "Không thể tải thông tin truyện.";
    exit;
}

$storyName = $storyData['data']['item']['name'] ?? 'Không rõ';
$chapters = $storyData['data']['item']['chapters'][0]['server_data'] ?? [];
$currentChapterUrl = $chapterUrl;

// Tìm vị trí chương hiện tại
$currentIndex = -1;
foreach ($chapters as $index => $chapter) {
    if ($chapter['chapter_api_data'] === $currentChapterUrl) {
        $currentIndex = $index;
        break;
    }
}

$prevChapterUrl = ($currentIndex > 0) ? base64_encode($chapters[$currentIndex - 1]['chapter_api_data']) : null;
$nextChapterUrl = ($currentIndex < count($chapters) - 1) ? base64_encode($chapters[$currentIndex + 1]['chapter_api_data']) : null;

// Lấy thông tin chapter từ dữ liệu API
$chapterItem = $chapterData['data']['item'];
$chapterName = $chapterItem['chapter_name'] ?? 'Chương không xác định';
$chapterTitle = $chapterItem['chapter_title'] ?? '';
$chapterImages = $chapterItem['chapter_image'] ?? [];
$domainCdn = $chapterData['data']['domain_cdn'] ?? 'https://sv1.otruyencdn.com';
$chapterPath = $chapterItem['chapter_path'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Đọc Truyện - <?php echo htmlspecialchars($storyName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .chapter-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .bottom-navigation {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            background-color: #1a1a1a;
            padding: 5px 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.5);
            border-radius: 5px;
        }
        .bottom-navigation button {
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 14px;
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .bottom-navigation button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .bottom-navigation button:hover:not(:disabled) {
            background-color: #0056b3;
        }
        .current-chapter {
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            margin: 0 10px;
            background-color: #333;
            padding: 5px 15px;
            border-radius: 3px;
        }
        .chapter-content {
            padding-bottom: 60px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            max-height: 80vh;
            overflow-y: auto;
            width: 90%;
            max-width: 400px;
        }
        .chapter-list-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .chapter-list-item:hover {
            background-color: #f5f5f5;
        }
        h1 {
            text-align: center;
            padding: 15px 20px;
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase; 
        }

        /* CSS cho giao diện sáng/tối */
        body {
            transition: background-color 0.3s, color 0.3s;
        }
        body.light-mode {
            background-color: #ffffff;
            color: #000000;
        }
        body.dark-mode {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        .bottom-navigation.light-mode {
            background-color: #f8f9fa;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }
        .bottom-navigation.dark-mode {
            background-color: #1a1a1a;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.5);
        }
        .modal-content.light-mode {
            background-color: #ffffff;
            color: #000000;
        }
        .modal-content.dark-mode {
            background-color: #333333;
            color: #ffffff;
        }
        .chapter-list-item.light-mode:hover {
            background-color: #f5f5f5;
        }
        .chapter-list-item.dark-mode:hover {
            background-color: #444444;
        }
    </style>
</head>
<body class="dark-mode"> <!-- Mặc định là dark mode -->
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <br><br><br><h1><?php echo htmlspecialchars($storyName); ?> - Chapter <?php echo htmlspecialchars($chapterName); ?> <?php echo $chapterTitle ? ' - ' . htmlspecialchars($chapterTitle) : ''; ?></h1>
        
        <div class="chapter-content">
            <?php
            if (!empty($chapterImages) && is_array($chapterImages)) {
                foreach ($chapterImages as $image) {
                    $imageFile = $image['image_file'] ?? '';
                    if ($imageFile) {
                        $imageUrl = $domainCdn . '/' . $chapterPath . '/' . $imageFile;
                        echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="Trang truyện">';
                    }
                }
            } else {
                echo '<p>Không có hình ảnh nào cho chương này.</p>';
            }
            ?>
        </div>

        <div class="chapter-navigation text-center"><hr>
            <a href="truyen-detail.php?slug=<?php echo urlencode($storySlug); ?>" class="btn btn-secondary">Quay lại danh sách chương</a><br>
        </div>
    </main><br>

    <?php include '../includes/footer.php'; ?>

    <!-- Thanh điều hướng dưới cùng -->
    <div class="bottom-navigation dark-mode">
        <button id="showChapterMenu">
            <i class="fas fa-bars"></i>
        </button>
        <button id="backChapter" <?php echo $prevChapterUrl ? '' : 'disabled'; ?> 
                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($prevChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'">
            <
        </button>
        <span class="current-chapter">CHƯƠNG <?php echo htmlspecialchars(strtoupper($chapterName)); ?></span>
        <button id="nextChapter" <?php echo $nextChapterUrl ? '' : 'disabled'; ?> 
                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($nextChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'">
            >
        </button>
        <button id="backToTop" onclick="scrollToTop()">
            ↑
        </button>
        <button id="toggleTheme" onclick="toggleTheme()">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <!-- Modal chọn chương nhanh -->
    <div id="chapterModal" class="modal">
        <div class="modal-content dark-mode">
            <h3>Chọn chương</h3>
            <div id="chapterList">
                <?php foreach ($chapters as $index => $chapter) : ?>
                    <div class="chapter-list-item" data-url="<?php echo htmlspecialchars(base64_encode($chapter['chapter_api_data'])); ?>" 
                         onclick="selectChapter('<?php echo htmlspecialchars(base64_encode($chapter['chapter_api_data'])); ?>')">
                        Chapter <?php echo htmlspecialchars($chapter['chapter_name']); ?> - <?php echo htmlspecialchars($chapter['chapter_title'] ?: 'N/A'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        const showChapterMenu = document.getElementById('showChapterMenu');
        const chapterModal = document.getElementById('chapterModal');
        const backChapterBtn = document.getElementById('backChapter');
        const nextChapterBtn = document.getElementById('nextChapter');
        const toggleThemeBtn = document.getElementById('toggleTheme');

        showChapterMenu.addEventListener('click', () => {
            chapterModal.style.display = 'block';
        });

        window.addEventListener('click', (event) => {
            if (event.target === chapterModal) {
                chapterModal.style.display = 'none';
            }
        });

        function selectChapter(encodedUrl) {
            window.location.href = `doc-truyen.php?chapter_url=${encodeURIComponent(encodedUrl)}&story_slug=<?php echo urlencode($storySlug); ?>`;
        }

        // Xử lý sự kiện nhấn phím Left và Right để chuyển chương
        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft' && !backChapterBtn.disabled) {
                backChapterBtn.click();
            } else if (event.key === 'ArrowRight' && !nextChapterBtn.disabled) {
                nextChapterBtn.click();
            }
        });

        // Chuyển đổi giao diện sáng/tối
        function toggleTheme() {
            const body = document.body;
            const bottomNav = document.querySelector('.bottom-navigation');
            const modalContent = document.querySelector('.modal-content');
            const isDarkMode = body.classList.contains('dark-mode');

            if (isDarkMode) {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                bottomNav.classList.remove('dark-mode');
                bottomNav.classList.add('light-mode');
                modalContent.classList.remove('dark-mode');
                modalContent.classList.add('light-mode');
                toggleThemeBtn.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('theme', 'light');
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                bottomNav.classList.remove('light-mode');
                bottomNav.classList.add('dark-mode');
                modalContent.classList.remove('light-mode');
                modalContent.classList.add('dark-mode');
                toggleThemeBtn.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Khôi phục giao diện từ localStorage khi tải trang
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            const bottomNav = document.querySelector('.bottom-navigation');
            const modalContent = document.querySelector('.modal-content');

            if (savedTheme === 'light') {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                bottomNav.classList.remove('dark-mode');
                bottomNav.classList.add('light-mode');  
                modalContent.classList.remove('dark-mode');
                modalContent.classList.add('light-mode');
                toggleThemeBtn.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                body.classList.add('dark-mode');
                bottomNav.classList.add('dark-mode');
                modalContent.classList.add('dark-mode');
                toggleThemeBtn.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    </script>
</body>
</html>