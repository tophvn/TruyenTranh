<?php
session_start();
include('../config/database.php'); 
require_once '../google-api/vendor/autoload.php';
include '../config/send_email.php';

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
<html lang="en">
<head>
    <meta charset="utf-8">
    <link href="../img/logo.png" rel="icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="../css/css-login-register.css">
</head>
<body>
    <div class="site-wrap d-md-flex align-items-stretch">
        <div class="bg-img" style="background-image: url('../img/login-2.png')"></div>
        <div class="form-wrap">
            <div class="form-inner">
                <h1 class="title">Đăng Nhập</h1>
                <p class="caption mb-4">Vui lòng nhập thông tin đăng nhập của bạn để tiếp tục.</p>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="" method="POST" class="pt-3" id="loginForm">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="login" id="login" placeholder="Tên đăng nhập hoặc Email" required>
                        <label for="login">Tên đăng nhập hoặc Email</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Mật khẩu" required>
                        <label for="password">Mật Khẩu</label>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label for="remember" class="form-check-label">Nhớ tài khoản</label>
                        </div>
                        <div><a href="forgot_password.php">Quên mật khẩu?</a></div>
                    </div>
                    <div class="g-recaptcha" data-sitekey="6LdMRIwqAAAAAIZlIaS2kTj9gAgWljC2VEfKaROG"></div>
                    <br/>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary">Đăng Nhập</button>
                    </div>
                    <div class="mb-2">Bạn chưa có tài khoản? <a href="register.php">Đăng ký</a></div>
                    <div class="social-account-wrap">
                        <h4 class="mb-4"><span>hoặc tiếp tục với</span></h4>
                        <ul class="list-unstyled social-account d-flex justify-content-between">
                            <li><a href="<?php echo $googleLoginUrl; ?>"><img src="../img/Icon/icon-google.svg" alt="Logo Google"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-facebook.svg" alt="Logo Facebook"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-apple.svg" alt="Logo Apple"></a></li>
                            <li><a href="#"><img src="../img/Icon/icon-twitter.svg" alt="Logo Twitter"></a></li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../../js/custom.js"></script>
    <a href="../index.php" class="btn" style="position: fixed; bottom: 20px; right: 20px; display: inline-flex; align-items: center; background-color: white; border: none; border-radius: 50%; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); width: 50px; height: 50px; justify-content: center; z-index: 1000;">
        <i class="uil uil-estate" style="font-size: 1.5rem; color: #007bff;"></i>
    </a>
    <script>
        $(document).on('submit', '#loginForm', function(event) {
            var response = grecaptcha.getResponse();
            if (response.length === 0) {
                alert("Vui lòng xác thực bạn không phải là robot");
                event.preventDefault();
            }
        });
    </script>
</body>
</html>