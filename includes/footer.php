<?php 
$base_dir = dirname(__DIR__);
$visitFile = $base_dir . '/visit_count.txt';
$activeUsersFile = $base_dir . '/active_users.txt';

if (!file_exists($visitFile)) {
    file_put_contents($visitFile, '0');
}
$visitCount = (int) file_get_contents($visitFile);
$visitCount++;
file_put_contents($visitFile, $visitCount);

$sessionStarted = session_status() === PHP_SESSION_ACTIVE ? true : false;
if (!$sessionStarted) {
    session_start();
}
$sessionLifetime = 300; 
$activeUsers = [];

if (file_exists($activeUsersFile)) {
    $activeUsers = unserialize(file_get_contents($activeUsersFile));
}
$currentTime = time();
foreach ($activeUsers as $sessionId => $time) {
    if ($currentTime - $time > $sessionLifetime) {
        unset($activeUsers[$sessionId]);
    }
}

$activeUsers[session_id()] = $currentTime;
file_put_contents($activeUsersFile, serialize($activeUsers));

$activeUserCount = count($activeUsers);
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/');
?>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Phần logo và thương hiệu -->
            <div class="footer-section logo-section">
                <a href="<?= $base_url; ?>" class="footer-logo">
                    <span class="logo-text">TRUYENTRANHNET</span>
                </a>
                <p class="footer-slogan">Kho truyện tranh miễn phí cập nhật 24/7</p>
            </div>
            <!-- Phần liên kết -->
            <div class="footer-section links-section">
                <h5 class="section-title">Liên Kết</h5>
                <ul class="footer-links">
                    <li><a href="<?= $base_url; ?>/views/ve-chung-toi.php" class="footer-link">Về Chúng Tôi</a></li>
                    <li><a href="<?= $base_url; ?>/views/chinh-sach-bao-mat.php" class="footer-link">Chính Sách Bảo Mật</a></li>
                    <li><a href="<?= $base_url; ?>/views/lien-he.php" class="footer-link">Liên Hệ</a></li>
                </ul>
            </div>
            <!-- Phần thông tin và lượt truy cập -->
            <div class="footer-section info-section">
                <h5 class="section-title">Thông Tin</h5>
                <p class="footer-info">© 2024 TRUYENTRANHNET</p>
                <p class="footer-visits"><i class="fas fa-eye"></i> Lượt truy cập: <span class="visit-count"><?= number_format($visitCount); ?></span></p>
                <p class="footer-active-users"><i class="fas fa-users"></i> Người đang truy cập: <span class="active-count"><?= number_format($activeUserCount); ?></span></p>
            </div>
        </div>
        <!-- Phần mạng xã hội -->
        <div class="footer-social">
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background: linear-gradient(135deg, #1a0033, #2a004d);
    color: #ffffff;
    padding: 40px 0;
    position: relative;
    overflow: hidden;
    border-top: 2px solid #00b7eb;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.footer-section {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 183, 235, 0.2);
    border: 1px solid rgba(0, 183, 235, 0.3);
    transition: all 0.3s ease;
}

.footer-section:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 183, 235, 0.4);
}

.logo-section .footer-logo {
    text-decoration: none;
}

.logo-text {
    font-size: 2rem;
    font-weight: 700;
    color: #00b7eb;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: color 0.3s ease;
}

.logo-text:hover {
    color: #00eaff;
}

.footer-slogan {
    font-size: 0.9rem;
    color: #a0c0ff;
    margin-top: 5px;
}

.section-title {
    font-size: 1.2rem;
    color: #00b7eb;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-link {
    color: #ffffff;
    text-decoration: none;
    font-size: 0.95rem;
    display: block;
    padding: 5px 0;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #00eaff;
    padding-left: 10px;
}

.footer-info {
    font-size: 0.85rem;
    color: #a0c0ff;
    margin: 5px 0;
}

.footer-visits {
    font-size: 0.9rem;
    color: #a0c0ff;
    margin: 5px 0;
}

.footer-active-users {
    font-size: 0.9rem;
    color: #a0c0ff;
    margin: 5px 0;
}

.visit-count, .active-count {
    font-weight: 600;
    color: #00eaff;
}

.footer-social {
    text-align: center;
    margin-top: 20px;
}

.social-icons {
    display: inline-flex;
    gap: 15px;
}

.social-icon {
    color: #a0c0ff;
    font-size: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.social-icon::before {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -5px;
    left: 50%;
    background: #00eaff;
    transition: all 0.3s ease;
}

.social-icon:hover {
    color: #00eaff;
    transform: scale(1.2);
}

.social-icon:hover::before {
    width: 100%;
    left: 0;
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }

    .logo-text {
        font-size: 1.5rem;
    }

    .footer-slogan,
    .footer-info,
    .footer-visits,
    .footer-active-users {
        font-size: 0.8rem;
    }

    .section-title {
        font-size: 1rem;
    }

    .footer-link {
        font-size: 0.9rem;
    }

    .social-icon {
        font-size: 1.2rem;
    }
}
</style>