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
    <title>404 - Không Tìm Thấy Trang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        /* CSS tùy chỉnh cho trang 404 */
        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, #f0f2f5 0%, #e0e7ff 100%);
            padding: 20px;
        }

        .error-content {
            max-width: 600px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .error-icon {
            font-size: 5rem;
            color: #ef4444;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 30px;
        }

        .btn-home {
            background: linear-gradient(90deg, #ffcd3c, #f4a261);
            color: #fff;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: linear-gradient(90deg, #e6b800, #e76f51);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .error-content {
                padding: 20px;
            }

            .error-icon {
                font-size: 3.5rem;
            }

            .error-title {
                font-size: 2rem;
            }

            .error-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="error-container">
        <div class="error-content">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h1 class="error-title">404 - Không Tìm Thấy Trang</h1>
            <p class="error-message">Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra lại URL hoặc quay về trang chủ.</p>
            <a href="/index.php" class="btn-home">Quay Về Trang Chủ</a>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script>
        document.querySelector('.hamburger-btn').addEventListener('click', function() {
            document.querySelector('.header').classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const header = document.querySelector('.header');
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            const navContainer = document.querySelector('.nav-container');

            if (!hamburgerBtn.contains(event.target) && !navContainer.contains(event.target) && header.classList.contains('active')) {
                header.classList.remove('active');
            }
        });
    </script>
</body>
</html>