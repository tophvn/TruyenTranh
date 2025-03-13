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

$story = $storyData['data']['item'];
$storyName = $story['name'] ?? 'Không rõ';
$chapters = $story['chapters'][0]['server_data'] ?? [];
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16">
        <div class="content-wrapper max-w-5xl mx-auto">
            <h1 class="text-center py-4 bg-gradient-to-r from-blue-500 to-blue-300 text-white rounded-lg shadow-lg mb-5 text-2xl font-bold uppercase">
                <?php echo htmlspecialchars($storyName); ?> - Chapter <?php echo htmlspecialchars($chapterName); ?> <?php echo $chapterTitle ? ' - ' . htmlspecialchars($chapterTitle) : ''; ?>
            </h1>

            <!-- Phần thông tin mới -->
            <div class="chapter-info mb-5">
                <!-- Breadcrumb -->
                <div class="text-gray-400 text-sm mb-2">
                    <a href="/" class="hover:underline">Trang Chủ</a> / 
                    <a href="truyen-tranh.php?slug=<?php echo urlencode($storySlug); ?>" class="hover:underline"><?php echo htmlspecialchars($storyName); ?></a> / 
                    Chương <?php echo htmlspecialchars($chapterName); ?>
                </div>

                <!-- Thông báo -->
                <p class="text-gray-300 mb-4 text-center">
                    Nếu không xem được truyện vui lòng đổi <span class="font-semibold text-yellow-400">"SERVER"</span> bên dưới
                </p>

                <!-- Nút Server -->
                <div class="flex justify-center space-x-3 mb-4">
                    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">Server 1</button>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Server VIP</button>
                    <button class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition flex items-center space-x-1">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                        <span>Báo Lỗi</span>
                    </button>
                </div>

                <!-- Thanh điều hướng -->
                <div class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 fill-current text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                        <span>Sử dụng mũi tên trái (←) hoặc phải (→) để chuyển chapter</span>
                    </div>
                    <div class="space-x-2">
                        <button id="prevChapterBtn" <?php echo $prevChapterUrl ? '' : 'disabled'; ?> 
                                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($prevChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                                class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                            ← Chap trước
                        </button>
                        <button id="nextChapterBtn" <?php echo $nextChapterUrl ? '' : 'disabled'; ?> 
                                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($nextChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                                class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Chap sau →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nội dung chương -->
            <div class="chapter-content pb-16 bg-gray-900">
                <?php
                if (!empty($chapterImages) && is_array($chapterImages)) {
                    foreach ($chapterImages as $image) {
                        $imageFile = $image['image_file'] ?? '';
                        if ($imageFile) {
                            $imageUrl = $domainCdn . '/' . $chapterPath . '/' . $imageFile;
                            echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="Trang truyện" class="max-w-full h-auto block mx-auto">';
                        }
                    }
                } else {
                    echo '<p class="text-center text-gray-400">Không có hình ảnh nào cho chương này.</p>';
                }
                ?>
            </div>

            <!-- Điều hướng chương -->
            <div class="chapter-navigation text-center mb-16 bg-gray-900 p-4 rounded-lg">
                <hr class="border-gray-600 mb-4">
                <div class="flex justify-center space-x-3">
                    <button id="prevChapterNav" <?php echo $prevChapterUrl ? '' : 'disabled'; ?> 
                            onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($prevChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                        ← Chương trước
                    </button>
                    <a href="truyen-tranh.php?slug=<?php echo urlencode($storySlug); ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Quay lại danh sách chương</a>
                    <button id="nextChapterNav" <?php echo $nextChapterUrl ? '' : 'disabled'; ?> 
                            onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($nextChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Chương sau →
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Thanh điều hướng dưới cùng -->
    <div id="bottomNavigation" class="bottom-navigation fixed bottom-0 left-1/2 transform -translate-x-1/2 bg-gray-800 p-2 flex justify-center items-center z-[1000] shadow-[0_-2px_5px_rgba(0,0,0,0.7)] rounded-lg">
        <button id="showChapterMenu" class="bg-blue-600 text-white text-sm px-3 py-1 mx-1 rounded hover:bg-blue-700 transition">
            <i class="fas fa-bars"></i>
        </button>
        <button id="backChapter" <?php echo $prevChapterUrl ? '' : 'disabled'; ?> 
                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($prevChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                class="bg-blue-600 text-white text-sm px-3 py-1 mx-1 rounded hover:bg-blue-700 transition disabled:bg-gray-500 disabled:cursor-not-allowed">
            <
        </button>
        <span class="current-chapter text-white font-bold mx-2 bg-gray-700 px-4 py-1 rounded flex-1 text-center">CHƯƠNG <?php echo htmlspecialchars(strtoupper($chapterName)); ?></span>
        <button id="nextChapter" <?php echo $nextChapterUrl ? '' : 'disabled'; ?> 
                onclick="window.location.href='doc-truyen.php?chapter_url=<?php echo urlencode($nextChapterUrl ?? ''); ?>&story_slug=<?php echo urlencode($storySlug); ?>'"
                class="bg-blue-600 text-white text-sm px-3 py-1 mx-1 rounded hover:bg-blue-700 transition disabled:bg-gray-500 disabled:cursor-not-allowed">
            >
        </button>
        <button id="backToTop" onclick="scrollToTop()" class="bg-blue-600 text-white text-sm px-3 py-1 mx-1 rounded hover:bg-blue-700 transition">
            ↑
        </button>
        <button id="toggleTheme" onclick="toggleTheme()" class="bg-blue-600 text-white text-sm px-3 py-1 mx-1 rounded hover:bg-blue-700 transition">
            <i class="fas fa-sun"></i>
        </button>
    </div> 

    <!-- Modal chọn chương nhanh -->
    <div id="chapterModal" class="modal hidden fixed inset-0 bg-black bg-opacity-80 z-[2000]">
        <div class="modal-content absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-800 p-5 rounded-lg max-h-[80vh] overflow-y-auto w-11/12 max-w-sm text-white relative">
            <button id="closeModal" class="absolute top-2 right-2 text-red-500 text-xl font-bold hover:text-red-400 transition">×</button>
            <h3 class="text-lg font-semibold mb-4">Chọn chương</h3>
            <div id="chapterList">
                <?php
                usort($chapters, function($a, $b) {
                    $chapterA = floatval(preg_replace('/[^0-9.]/', '', $a['chapter_name']));
                    $chapterB = floatval(preg_replace('/[^0-9.]/', '', $b['chapter_name']));
                    return $chapterB - $chapterA;
                });

                foreach ($chapters as $index => $chapter) : ?>
                    <div class="chapter-list-item p-2 cursor-pointer border-b border-gray-600 hover:bg-gray-700 transition" 
                        data-url="<?php echo htmlspecialchars(base64_encode($chapter['chapter_api_data'])); ?>" 
                        onclick="selectChapter('<?php echo htmlspecialchars(base64_encode($chapter['chapter_api_data'])); ?>')">
                        Chapter <?php echo htmlspecialchars($chapter['chapter_name']); ?> 
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    const showChapterMenu = document.getElementById('showChapterMenu');
    const chapterModal = document.getElementById('chapterModal');
    const backChapterBtn = document.getElementById('backChapter');
    const nextChapterBtn = document.getElementById('nextChapter');
    const toggleThemeBtn = document.getElementById('toggleTheme');
    const prevChapterBtn = document.getElementById('prevChapterBtn');
    const nextChapterBtn2 = document.getElementById('nextChapterBtn');
    const bottomNav = document.getElementById('bottomNavigation');
    showChapterMenu.addEventListener('click', () => {
        chapterModal.classList.remove('hidden');
    });

    window.addEventListener('click', (event) => {
        if (event.target === chapterModal) {
            chapterModal.classList.add('hidden');
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

    // Ẩn thanh điều hướng sau 10 giây
    let hideTimeout;
    function hideBottomNav() {
        clearTimeout(hideTimeout); 
        hideTimeout = setTimeout(() => {
            bottomNav.classList.add('hidden');
        }, 10000); // 10 giây
    }

    // Hiện thanh điều hướng khi click vào màn hình
    document.addEventListener('click', (event) => {
        if (!bottomNav.contains(event.target) && !chapterModal.contains(event.target)) {
            bottomNav.classList.remove('hidden');
            hideBottomNav(); // Reset lại timer khi hiện
        }
    });
    hideBottomNav();

    // Chuyển đổi giao diện sáng/tối
    function toggleTheme() {
        const body = document.body;
        const main = document.querySelector('main');
        const contentWrapper = document.querySelector('.content-wrapper');
        const bottomNav = document.querySelector('.bottom-navigation');
        const chapterContent = document.querySelector('.chapter-content');
        const chapterNavigation = document.querySelector('.chapter-navigation');
        const modalContent = document.querySelector('.modal-content');
        const chapterInfo = document.querySelector('.chapter-info');
        const isDarkMode = body.classList.contains('dark-mode');

        if (isDarkMode) {
            body.classList.remove('bg-gray-900', 'text-white', 'dark-mode');
            body.classList.add('bg-white', 'text-black', 'light-mode');
            main.classList.remove('bg-gray-900');
            main.classList.add('bg-white');
            contentWrapper.classList.remove('bg-gray-900');
            contentWrapper.classList.add('bg-white');
            bottomNav.classList.remove('bg-gray-800', 'shadow-[0_-2px_5px_rgba(0,0,0,0.7)]');
            bottomNav.classList.add('bg-gray-100', 'shadow-[0_-2px_5px_rgba(0,0,0,0.1)]');
            chapterContent.classList.remove('bg-gray-900');
            chapterContent.classList.add('bg-white');
            chapterNavigation.classList.remove('bg-gray-900');
            chapterNavigation.classList.add('bg-white');
            modalContent.classList.remove('bg-gray-800', 'text-white');
            modalContent.classList.add('bg-white', 'text-black', 'border', 'border-gray-300');
            chapterInfo.querySelectorAll('p').forEach(p => {
                p.classList.remove('text-gray-300');
                p.classList.add('text-gray-700');
            });
            toggleThemeBtn.innerHTML = '<i class="fas fa-moon"></i>';
            localStorage.setItem('theme', 'light');
        } else {
            body.classList.remove('bg-white', 'text-black', 'light-mode');
            body.classList.add('bg-gray-900', 'text-white', 'dark-mode');
            main.classList.remove('bg-white');
            main.classList.add('bg-gray-900');
            contentWrapper.classList.remove('bg-white');
            contentWrapper.classList.add('bg-gray-900');
            bottomNav.classList.remove('bg-gray-100', 'shadow-[0_-2px_5px_rgba(0,0,0,0.1)]');
            bottomNav.classList.add('bg-gray-800', 'shadow-[0_-2px_5px_rgba(0,0,0,0.7)]');
            chapterContent.classList.remove('bg-white');
            chapterContent.classList.add('bg-gray-900');
            chapterNavigation.classList.remove('bg-white');
            chapterNavigation.classList.add('bg-gray-900');
            modalContent.classList.remove('bg-white', 'text-black', 'border', 'border-gray-300');
            modalContent.classList.add('bg-gray-800', 'text-white');
            chapterInfo.querySelectorAll('p').forEach(p => {
                p.classList.remove('text-gray-700');
                p.classList.add('text-gray-300');
            });
            toggleThemeBtn.innerHTML = '<i class="fas fa-sun"></i>';
            localStorage.setItem('theme', 'dark');
        }
    }

    // Khôi phục giao diện từ localStorage khi tải trang
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme');
        const body = document.body;
        const main = document.querySelector('main');
        const contentWrapper = document.querySelector('.content-wrapper');
        const bottomNav = document.querySelector('.bottom-navigation');
        const chapterContent = document.querySelector('.chapter-content');
        const chapterNavigation = document.querySelector('.chapter-navigation');
        const modalContent = document.querySelector('.modal-content');
        const chapterInfo = document.querySelector('.chapter-info');

        if (savedTheme === 'light') {
            body.classList.remove('bg-gray-900', 'text-white', 'dark-mode');
            body.classList.add('bg-white', 'text-black', 'light-mode');
            main.classList.remove('bg-gray-900');
            main.classList.add('bg-white');
            contentWrapper.classList.remove('bg-gray-900');
            contentWrapper.classList.add('bg-white');
            bottomNav.classList.remove('bg-gray-800', 'shadow-[0_-2px_5px_rgba(0,0,0,0.7)]');
            bottomNav.classList.add('bg-gray-100', 'shadow-[0_-2px_5px_rgba(0,0,0,0.1)]');
            chapterContent.classList.remove('bg-gray-900');
            chapterContent.classList.add('bg-white');
            chapterNavigation.classList.remove('bg-gray-900');
            chapterNavigation.classList.add('bg-white');
            modalContent.classList.remove('bg-gray-800', 'text-white');
            modalContent.classList.add('bg-white', 'text-black', 'border', 'border-gray-300');
            chapterInfo.querySelectorAll('p').forEach(p => {
                p.classList.remove('text-gray-300');
                p.classList.add('text-gray-700');
            });
            toggleThemeBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            body.classList.add('bg-gray-900', 'text-white', 'dark-mode');
            main.classList.add('bg-gray-900');
            contentWrapper.classList.add('bg-gray-900');
            bottomNav.classList.add('bg-gray-800', 'shadow-[0_-2px_5px_rgba(0,0,0,0.7)]');
            chapterContent.classList.add('bg-gray-900');
            chapterNavigation.classList.add('bg-gray-900');
            modalContent.classList.add('bg-gray-800', 'text-white');
            chapterInfo.querySelectorAll('p').forEach(p => {
                p.classList.add('text-gray-300');
            });
            toggleThemeBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
    });
    // Thêm sự kiện cho nút đóng modal
    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            document.getElementById('chapterModal').classList.add('hidden');
        });
    }
</script>
</body>
</html>