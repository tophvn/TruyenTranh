<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Liên Hệ - TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include '../includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-16 flex items-center justify-center min-h-screen">
        <div class="content-wrapper max-w-md w-full bg-gray-800 p-6 rounded-lg shadow-lg">
            <h1 class="text-center text-3xl font-bold mb-6">Liên Hệ Với Chúng Tôi</h1>
            
            <h2 class="text-2xl font-semibold mb-4 text-center">Thông Tin Liên Hệ</h2>
            <p class="text-gray-300 mb-6 text-center">Để biết thêm thông tin, bạn có thể liên hệ với chúng tôi qua trang Facebook dưới đây:</p>
            
            <div class="facebook-profile flex flex-col items-center">
                <a href="https://www.facebook.com/tophvn" target="_blank" class="flex flex-col items-center text-blue-400 hover:text-blue-300">
                    <img src="https://www.facebook.com/images/fb_icon_325x325.png" alt="Toph VN Facebook" class="rounded-full w-24 h-24 mb-4">
                    <h3 class="text-xl font-medium">Toph VN</h3>
                </a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script>
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