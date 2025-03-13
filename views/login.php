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
        // Mã hóa login để so sánh với username trong database
        $hashedLogin = md5($login);

        // Dùng prepared statement để kiểm tra username (đã mã hóa) hoặc email (nguyên bản)
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $hashedLogin, $login); // So sánh hashedLogin với username, login với email
        $stmt->execute();
        $result = $stmt->get_result();

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
        $stmt->close();
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
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-gray-900 text-white dark-mode min-h-screen transition-all duration-300">
    <?php include('../includes/header.php'); ?>

    <main class="container mx-auto px-4 py-8 pt-16 flex items-center justify-center min-h-screen">
        <div class="content-wrapper max-w-md w-full bg-gray-800 p-6 rounded-lg shadow-lg">
            <h1 class="text-center text-3xl font-bold mb-4">Đăng Nhập</h1>
            <?php if (!empty($errors)): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4">
                    <?php foreach ($errors as $error): ?>
                        <p class="text-sm"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4" id="loginForm">
                <div>
                    <label for="login" class="block text-sm font-medium mb-1">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="login" name="login" placeholder="Tên đăng nhập hoặc Email" required
                           class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Mật khẩu" required
                           class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="mr-2 accent-blue-500">
                        <label for="remember" class="text-sm text-gray-400">Nhớ tài khoản</label>
                    </div>
                    <a href="forgot_password.php" class="text-sm text-blue-400 hover:text-blue-300">Quên mật khẩu?</a>
                </div>
                <div class="g-recaptcha mb-4" data-sitekey="6LdMRIwqAAAAAIZlIaS2kTj9gAgWljC2VEfKaROG"></div>
                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    Đăng Nhập
                </button>
                <div class="text-center text-sm text-gray-400">
                    Bạn chưa có tài khoản? <a href="register.php" class="text-blue-400 hover:text-blue-300">Đăng ký</a>
                </div>
                <div class="text-center my-4">Hoặc tiếp tục với</div>
                <div class="flex justify-between space-x-2">
                    <a href="<?php echo $googleLoginUrl; ?>" class="flex-1 bg-gray-700 p-2 rounded-lg text-center hover:bg-gray-600 transition">
                        <img src="../img/Icon/icon-google.svg" alt="Google" class="w-6 h-6 mx-auto">
                    </a>
                    <!-- <a href="#" class="flex-1 bg-gray-700 p-2 rounded-lg text-center hover:bg-gray-600 transition">
                        <img src="../img/Icon/icon-facebook.svg" alt="Facebook" class="w-6 h-6 mx-auto">
                    </a>
                    <a href="#" class="flex-1 bg-gray-700 p-2 rounded-lg text-center hover:bg-gray-600 transition">
                        <img src="../img/Icon/icon-apple.svg" alt="Apple" class="w-6 h-6 mx-auto">
                    </a>
                    <a href="#" class="flex-1 bg-gray-700 p-2 rounded-lg text-center hover:bg-gray-600 transition">
                        <img src="../img/Icon/icon-twitter.svg" alt="Twitter" class="w-6 h-6 mx-auto">
                    </a> -->
                </div>
            </form>
        </div>
    </main>

    <?php include('../includes/footer.php'); ?>

    <a href="../index.php" class="fixed bottom-6 right-6 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-gray-200 transition duration-300 z-50">
        <i class="uil uil-estate text-blue-600 text-xl"></i>
    </a>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                alert("Vui lòng xác thực bạn không phải là robot");
                event.preventDefault();
            }
        });

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