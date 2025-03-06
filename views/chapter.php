<?php
// Kiểm tra xem dữ liệu có được gửi qua phương thức POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapterApiUrl = isset($_POST['chapter_url']) ? $_POST['chapter_url'] : '';

    if (!$chapterApiUrl) {
        echo "Không có URL chương để tải.";
        exit;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $chapterApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $chapterData = json_decode($response, true);

    if ($response === false || !$chapterData || !isset($chapterData['data']['item'])) {
        $error = "Có lỗi xảy ra! Vui lòng reload lại trang!";
        $chapterItem = null;
    } else {
        $chapterItem = $chapterData['data']['item'];
        $cdnDomain = $chapterData['data']['domain_cdn'];
    }
} else {
    echo "Yêu cầu không hợp lệ.";
    exit;
}
?>

<?php if (isset($error)): ?>
    <p class="alert alert-danger"><?php echo htmlspecialchars($error); ?></p>
<?php else: ?>
    <h1><?php echo htmlspecialchars($chapterItem['comic_name'] ?? 'N/A'); ?> [<?php echo htmlspecialchars($chapterItem['chapter_name'] ?? 'N/A'); ?>]</h1>
    <div class="chapter-images">
        <?php
        if (isset($chapterItem['chapter_image']) && is_array($chapterItem['chapter_image'])) {
            foreach ($chapterItem['chapter_image'] as $image) {
                $imageUrl = $cdnDomain . '/' . $chapterItem['chapter_path'] . '/' . $image['image_file'];
                echo '<div class="mb-4"><img src="' . htmlspecialchars($imageUrl) . '" class="img-fluid" alt="Trang ' . htmlspecialchars($image['image_page']) . '"></div>';
            }
        } else {
            echo "<p>Không có hình ảnh cho chương này.</p>";
        }
        ?>
    </div>
<?php endif; ?>
