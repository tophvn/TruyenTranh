<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//$base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/views';

// Lấy đường dẫn tương đối từ thư mục gốc
$base_url = dirname($_SERVER['SCRIPT_NAME'], substr_count($_SERVER['SCRIPT_NAME'], '/') - 1);

$api_url = "https://otruyenapi.com/v1/api/the-loai";

// Sử dụng cURL để gọi API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
curl_close($ch);

// Chuyển đổi dữ liệu JSON thành mảng PHP
$data = json_decode($response, true);

// Kiểm tra dữ liệu trả về có hợp lệ không
if (isset($data['data']) && !empty($data['data']['items'])) {
    $categories = $data['data']['items']; // Lấy danh sách thể loại truyện
} else {
    $categories = []; // Nếu không có dữ liệu, trả về mảng trống
}

// Hàm để xử lý đường dẫn avatar
function getAvatarPath($avatar) {
    return ltrim($avatar, '../');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRUYENTRANHNET</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- CSS trực tiếp -->
    <style>
        /* Reset mặc định */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #2d3748;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 12px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-brand {
            font-size: 1.8rem;
            color: #fef08a;
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 1px;
            transition: color 0.3s ease;
        }

        .header-brand:hover {
            color: #fff;
        }

        .hamburger-btn {
            display: none;
            font-size: 1.6rem;
            color: #fef08a;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }

        .search-bar {
            display: flex;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .search-bar input {
            border: none;
            padding: 10px 15px;
            border-radius: 25px 0 0 25px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            font-size: 0.95rem;
            width: 200px;
            transition: width 0.3s ease;
        }

        .search-bar input:focus {
            width: 250px;
            outline: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .search-bar button {
            border: none;
            padding: 10px 15px;
            border-radius: 0 25px 25px 0;
            background: #ef4444;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-bar button:hover {
            background: #dc2626;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .user-actions .dropdown-toggle {
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            transition: background 0.3s ease;
        }

        .user-actions .dropdown-toggle img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #fef08a;
        }

        .user-actions .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            padding: 5px 0;
        }

        .user-actions .dropdown:hover .dropdown-menu {
            display: block;
        }

        .user-actions .dropdown-item {
            padding: 8px 15px;
            color: #1e3a8a;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .user-actions .dropdown-item:hover {
            background: #3b82f6;
            color: #fff;
        }

        .user-actions .login-btn,
        .user-actions .register-btn {
            background: #fef08a;
            color: #1e3a8a;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .user-actions .login-btn:hover,
        .user-actions .register-btn:hover {
            background: #facc15;
            color: #1e3a8a;
        }

        /* Genre Navigation */
        .genre-nav {
            background: #fff;
            padding: 15px 0;
            margin-top: 70px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: block; /* Đảm bảo hiển thị mặc định */
        }

        .nav {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            list-style: none; /* Loại bỏ dấu chấm đầu dòng */
        }

        .nav-link {
            color: #1e3a8a !important;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            background: #e0f2fe;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block; /* Đảm bảo hiển thị đúng */
        }

        .nav-link:hover {
            background: #3b82f6;
            color: #fff !important;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            padding: 10px;
            width: 600px;
            max-height: 300px;
            overflow-y: auto;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            z-index: 1000;
        }

        .dropdown-item {
            padding: 8px 12px;
            color: #1e3a8a;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-align: center;
            border-radius: 6px;
        }

        .dropdown-item:hover {
            background: #3b82f6;
            color: #fff;
        }

        marquee {
            background: #fef08a;
            color: #1e3a8a;
            padding: 8px;
            font-weight: 500;
            border-radius: 8px;
            margin: 10px 0;
        }

        /* Mobile Navigation trong hamburger */
        .mobile-nav {
            display: none;
            list-style: none; /* Loại bỏ dấu chấm đầu dòng */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 10px 15px;
            }

            .header-brand {
                font-size: 1.5rem;
            }

            .hamburger-btn {
                display: block;
            }

            .nav-container {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #1e3a8a;
                padding: 20px; /* Tăng padding để trông chuyên nghiệp hơn */
                flex-direction: column;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
                border-radius: 0 0 10px 10px;
                z-index: 999;
                text-align: center; /* Căn giữa nội dung */
            }

            .header.active .nav-container {
                display: flex;
            }

            .search-bar {
                position: static;
                transform: none;
                width: 100%;
                margin-bottom: 15px;
                justify-content: center; /* Căn giữa thanh tìm kiếm */
            }

            .search-bar input {
                width: 80%;
                border-radius: 25px 0 0 25px;
                margin-bottom: 0;
                padding: 12px 15px; /* Tăng padding cho đẹp */
            }

            .search-bar button {
                border-radius: 0 25px 25px 0;
                padding: 12px 15px; /* Tăng padding cho đồng đều */
            }

            .user-actions {
                display: none;
                flex-direction: column;
                width: 100%;
                gap: 10px;
                margin-left: 0;
                margin-top: 15px; /* Thêm khoảng cách trên */
            }

            .header.active .user-actions {
                display: flex;
            }

            .user-actions .dropdown-toggle,
            .user-actions .login-btn,
            .user-actions .register-btn {
                width: 80%;
                margin: 0 auto; /* Căn giữa */
                text-align: center;
                justify-content: center;
                background: #3b82f6;
                color: #fff;
                border-radius: 25px; /* Bo góc tròn hơn */
                padding: 12px 20px; /* Tăng padding cho đẹp */
                transition: all 0.3s ease;
            }

            .user-actions .login-btn,
            .user-actions .register-btn {
                background: #fef08a;
                color: #1e3a8a;
            }

            .user-actions .dropdown-toggle:hover,
            .user-actions .login-btn:hover,
            .user-actions .register-btn:hover {
                background: #2563eb; /* Màu hover đẹp hơn */
                color: #fff;
            }

            .user-actions .dropdown-menu {
                display: none;
                position: static;
                width: 80%;
                margin: 0 auto;
                box-shadow: none;
                border-radius: 10px;
                background: #2563eb;
            }

            .user-actions .dropdown:hover .dropdown-menu {
                display: block;
            }

            .user-actions .dropdown-item {
                color: #fff;
                text-align: center;
                padding: 10px 15px;
                border-radius: 5px;
            }

            .user-actions .dropdown-item:hover {
                background: #1e3a8a;
            }

            /* Ẩn .genre-nav khi hamburger mở */
            .header.active + .genre-nav {
                display: none;
            }

            .mobile-nav {
                display: none;
                flex-direction: column;
                align-items: center; /* Căn giữa các nút */
                gap: 10px; /* Khoảng cách đều hơn */
                width: 80%; /* Giới hạn chiều rộng */
                margin: 15px auto 0; /* Căn giữa và thêm khoảng cách trên */
                padding: 0; /* Loại bỏ padding thừa */
            }

            .header.active .mobile-nav {
                display: flex;
            }

            .mobile-nav .nav-item {
                width: 100%;
            }

            .mobile-nav .nav-link {
                background: #3b82f6;
                color: #fff !important;
                padding: 12px 20px;
                border-radius: 25px; /* Bo góc tròn hơn */
                width: 100%;
                text-align: center;
                display: block;
                transition: all 0.3s ease; /* Thêm hiệu ứng mượt */
                font-weight: 600; /* Chữ đậm hơn */
            }

            .mobile-nav .nav-link:hover {
                background: #2563eb;
                transform: translateY(-2px); /* Hiệu ứng nhấc lên */
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); /* Thêm bóng đổ */
            }

            .mobile-nav .dropdown-menu {
                display: none;
                position: static;
                width: 100%;
                background: #2563eb;
                box-shadow: none;
                border-radius: 10px;
                padding: 5px 0;
                margin-top: 5px; /* Khoảng cách với nút THỂ LOẠI */
            }

            .mobile-nav .dropdown:hover .dropdown-menu {
                display: block;
            }

            .mobile-nav .dropdown-item {
                color: #fff;
                padding: 10px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                text-align: center;
                border-radius: 5px;
                transition: all 0.3s ease;
            }

            .mobile-nav .dropdown-item:hover {
                background: #1e3a8a;
                transform: translateX(5px); /* Hiệu ứng trượt sang phải */
            }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container d-flex align-items-center justify-content-between w-100">
        <a class="header-brand text-warning fw-bold" href="<?= $base_url; ?>/index.php">TRUYENTRANHNET</a>
        <button class="hamburger-btn"><i class="fas fa-bars"></i></button>
        <div class="nav-container">
            <form method="GET" action="<?= $base_url; ?>/views/tim-kiem.php" class="search-bar">
                <input type="text" name="keyword" placeholder="Tìm kiếm truyện..." required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <!-- Thêm menu mobile vào hamburger -->
            <ul class="mobile-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url; ?>/views/truyen-moi.php">TRUYỆN MỚI</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url; ?>/views/dang-phat-hanh.php">ĐANG PHÁT HÀNH</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url; ?>/views/hoan-thanh.php">HOÀN THÀNH</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_url; ?>/views/sap-ra-mat.php">SẮP RA MẮT</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        THỂ LOẠI
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMobile">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <li><a class="dropdown-item" href="<?= $base_url; ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']); ?>"><?= htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="#">Không có thể loại</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
            <div class="user-actions">
                <?php if (isset($_SESSION['user']['user_id'])): ?>
                    <div class="dropdown">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= !empty($_SESSION['user']['avatar']) ? htmlspecialchars($_SESSION['user']['avatar']) : '../img/default-avatar.jpg'; ?>" alt="Avatar" class="rounded-circle">
                            <?= htmlspecialchars($_SESSION['user']['name']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="<?= $base_url; ?>/views/tai-khoan.php">Tài khoản</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url; ?>/views/following.php">Theo dõi</a></li>
                            <li><a class="dropdown-item" href="<?= $base_url; ?>/views/lich-su-doc.php">Lịch sử đọc</a></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base_url; ?>/views/logout.php">Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $base_url; ?>/views/login.php" class="login-btn">Đăng nhập</a>
                    <a href="<?= $base_url; ?>/views/register.php" class="register-btn">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="genre-nav py-2" style="margin-top: 70px;">
    <div class="container">
        <marquee style="color: red;">
            TRUYENTRANHNET BY TOPH. ĐỌC TRUYỆN MIỄN PHÍ KHÔNG QUẢNG CÁO, TRUYỆN TRANH CẬP NHẬT 24/7.
        </marquee>
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= $base_url; ?>/views/truyen-moi.php">TRUYỆN MỚI</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base_url; ?>/views/dang-phat-hanh.php">ĐANG PHÁT HÀNH</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base_url; ?>/views/hoan-thanh.php">HOÀN THÀNH</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= $base_url; ?>/views/sap-ra-mat.php">SẮP RA MẮT</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    THỂ LOẠI
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <li><a class="dropdown-item" href="<?= $base_url; ?>/views/truyen-theo-the-loai.php?slug=<?= htmlspecialchars($category['slug']); ?>"><?= htmlspecialchars($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="#">Không có thể loại</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </div>
</div>

<script>
    document.querySelector('.hamburger-btn').addEventListener('click', function() {
        document.querySelector('.header').classList.toggle('active');
    });
</script>
</body>
</html>