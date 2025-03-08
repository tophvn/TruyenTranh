<?php
session_start();
include('../config/database.php'); 
require_once '../google-api/vendor/autoload.php';
include('../config/send_email.php');

// Cấu hình OAuth 2.0
$clientID = '614640831923-ri38v149j4aitt9dmc2hql9trfo0v4uq.apps.googleusercontent.com'; 
$clientSecret = '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';
$redirectUri = 'http://localhost/TruyenTranh/views/login.php'; 

// Cấu hình Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
$errors = [];

// Secret key của Google reCAPTCHA
$recaptchaSecretKey = '6LdMRIwqAAAAAACZCDqKm0LlTYnQjz2OsUaNCh95';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (empty($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_account_info = (new Google_Service_Oauth2($client))->userinfo->get();

        // Lấy thông tin người dùng từ Google
        $email = $google_account_info->email;
        $google_user_id = $google_account_info->id;
        $username_md5 = md5($email);
        $avatar = $google_account_info->picture;

        // Kiểm tra nếu người dùng đã tồn tại
        $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if ($result->num_rows == 0) {
            $name = $google_account_info->name;
            $defaultPassword = md5(uniqid());
            $conn->query("INSERT INTO users (username, email, name, password, roles, avatar) 
                VALUES ('$username_md5', '$email', '$name', '$defaultPassword', 'user', '$avatar')");
        } else {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];
            $name = $user['name'];
            $avatar = $user['avatar'];
        }

        // Lưu thông tin đăng nhập vào session
        $_SESSION['user'] = [
            'user_id' => $user_id ?? $conn->insert_id, 
            'username' => $username_md5,
            'name' => $name,
            'roles' => $user['roles'] ?? 'user',
            'avatar' => $avatar,
        ];

        error_log("Login with Google successful, user_id: " . $_SESSION['user']['user_id']);
        header("Location: ../index.php");
        exit();
    } else {
        $errors[] = 'Đăng nhập Google thất bại!';
    }
}

// Xử lý đăng nhập thông thường
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra Google reCAPTCHA
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $recaptchaURL = "https://www.google.com/recaptcha/api/siteverify";
    $recaptchaValidation = json_decode(file_get_contents($recaptchaURL . "?secret=" . $recaptchaSecretKey . "&response=" . $recaptchaResponse), true);

    if (!$recaptchaValidation['success']) {
        $errors[] = 'Xác minh reCAPTCHA thất bại. Vui lòng thử lại!';
    } else {
        $result = $conn->query("SELECT * FROM users WHERE (username = '$login' OR email = '$login')");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (md5($password) === $user['password']) {
                $_SESSION['user'] = [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'roles' => $user['roles'],
                    'avatar' => $user['avatar'],
                ];

                error_log("Login with form successful, user_id: " . $_SESSION['user']['user_id']);
                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = 'Sai mật khẩu!';
            }
        } else {
            $errors[] = 'Sai tên người dùng hoặc email!';
        }
    }
}

// Tạo URL đăng nhập Google
$googleLoginUrl = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../img/logo.png" rel="icon">
    <title>Đăng Nhập - TRUYENTRANHNET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="../css/css-login-register.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#2a2e8a',
                        'button-primary': '#4CAF50',
                        'button-hover': '#45a049',
                        'accent': '#ffffff',
                        'hover-bg': '#e0e7ff',
                        'button-glow': '#80e27e',
                    },
                    animation: {
                        'slide-down': 'slide-down 0.3s ease-out',
                        'fade-in': 'fade-in 0.2s ease-in-out',
                        'pulse-glow': 'pulse-glow 2s infinite ease-in-out',
                    },
                    keyframes: {
                        'slide-down': {
                            '0%': { transform: 'translateY(-100%)', opacity: 0 },
                            '100%': { transform: 'translateY(0)', opacity: 1 },
                        },
                        'fade-in': {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 5px rgba(128, 226, 126, 0.3)' },
                            '50%': { boxShadow: "0 0 15px rgba(128, 226, 126, 0.7)" },
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-['Inter'] pt-16 lg:pt-20">
    <?php include('../includes/header.php'); ?>
    <div class="site-wrap d-md-flex align-items-stretch min-h-screen">
        <div class="bg-img" style="background-image: url('../img/login-2.png')"></div>
        <div class="form-wrap">
            <div class="form-inner p-4 sm:p-6 md:p-8">
                <h1 class="title text-3xl sm:text-4xl md:text-5xl mb-4">Đăng Nhập</h1>
                <p class="caption mb-4 text-sm sm:text-base">Vui lòng nhập thông tin đăng nhập của bạn để tiếp tục.</p>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="" method="POST" class="pt-3" id="loginForm">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="login" id="login" placeholder="Tên đăng nhập hoặc Email" required>
                        <label for="login">Tên đăng nhập hoặc Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Mật khẩu" required>
                        <label for="password">Mật Khẩu</label>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label for="remember" class="form-check-label">Nhớ tài khoản</label>
                        </div>
                        <div><a href="forgot_password.php">Quên mật khẩu?</a></div>
                    </div>
                    <div class="g-recaptcha mb-4" data-sitekey="6LdMRIwqAAAAAIZlIaS2kTj9gAgWljC2VEfKaROG"></div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary">Đăng Nhập</button>
                    </div>
                    <div class="mb-2 text-center">Bạn chưa có tài khoản? <a href="register.php">Đăng ký</a></div>
                    <div class="social-account-wrap">
                        <h4 class="mb-4"><span>hoặc tiếp tục với</span></h4>
                        <ul class="list-unstyled social-account d-flex justify-content-between">
                            <li><a href="<?php echo $googleLoginUrl; ?>"><img src="../img/Icon/icon-google.svg" alt="Google"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-facebook.svg" alt="Facebook"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-apple.svg" alt="Apple"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-twitter.svg" alt="Twitter"></a></li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <a href="../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).on('submit', '#loginForm', function(event) {
            var response = grecaptcha.getResponse();
            if (response.length === 0) {
                alert("Vui lòng xác thực bạn không phải là robot");
                event.preventDefault();
            }
        });
        document.getElementById('hamburger').addEventListener('click', function() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('hidden');
            if (!navMenu.classList.contains('hidden')) {
                navMenu.classList.add('animate-slide-down');
                setTimeout(() => navMenu.classList.remove('animate-slide-down'), 300);
            }
        });
    </script>
</body>
</html>