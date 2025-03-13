<?php
include('../config/database.php');
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>404 - Không Tìm Thấy Trang - TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16 flex items-center justify-center min-h-screen">
        <div class="content-wrapper max-w-md w-full bg-gray-800 p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
            <h1 class="text-3xl font-bold mb-4">404 - Không Tìm Thấy Trang</h1>
            <p class="text-gray-400 mb-6">Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra lại URL hoặc quay về trang chủ.</p>
            <a href="/index.php" class="inline-block bg-blue-600 text-white p-2 px-6 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
                Quay Về Trang Chủ
            </a>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <a href="../index.php" class="fixed bottom-6 right-6 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-gray-200 transition duration-300 z-50">
        <i class="uil uil-estate text-blue-600 text-xl"></i>
    </a>

    <script>
        // Xử lý menu hamburger (giả định trong header.php)
        const hamburger = document.getElementById('hamburger');
        if (hamburger) {
            hamburger.addEventListener('click', function() {
                const navMenu = document.getElementById('nav-menu');
                if (navMenu) {
                    navMenu.classList.toggle('hidden');
                    if (!navMenu.classList.contains('hidden')) {
                        navMenu.classList.add('animate-slide-down');
                        setTimeout(() => navMenu.classList.remove('animate-slide-down'), 300);
                    }
                }
            });
        }
    </script>
</body>
</html>