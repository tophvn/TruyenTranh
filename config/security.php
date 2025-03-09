<?php
// security.php
session_start();

// **Hàm lấy IP của client**
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
}

// **Rate Limiting: Giới hạn tần suất yêu cầu**
$rateLimit = 100; // Số yêu cầu tối đa trong 1 phút
$timeWindow = 60; // Thời gian (giây)

$clientIP = getClientIP();

if (!isset($_SESSION['request_count'])) {
    $_SESSION['request_count'] = [];
}

if (!isset($_SESSION['request_count'][$clientIP])) {
    $_SESSION['request_count'][$clientIP] = ['count' => 0, 'start_time' => time()];
}

// Reset nếu vượt quá thời gian
if (time() - $_SESSION['request_count'][$clientIP]['start_time'] > $timeWindow) {
    $_SESSION['request_count'][$clientIP] = ['count' => 0, 'start_time' => time()];
}

// Tăng số yêu cầu và kiểm tra giới hạn
$_SESSION['request_count'][$clientIP]['count']++;
if ($_SESSION['request_count'][$clientIP]['count'] > $rateLimit) {
    http_response_code(429); // Too Many Requests
    header('Retry-After: 60'); // Yêu cầu thử lại sau 60 giây
    die("Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau 1 phút.");
}

// **Ghi log hành vi bất thường**
function logSuspiciousActivity($ip, $message) {
    $logDir = '../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/ddos_log.txt';
    $logMessage = date('Y-m-d H:i:s') . " - IP: $ip - $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SESSION['request_count'][$clientIP]['count'] > ($rateLimit * 0.8)) {
    logSuspiciousActivity($clientIP, "Cảnh báo: Số lượng yêu cầu cao, có khả năng là DDoS.");
}

// **Kiểm tra IP đen (Blacklist)**
$blacklist = []; // Có thể tải từ file hoặc DB, ví dụ: ['192.168.1.1', '203.0.113.0']
if (file_exists($logDir . '/blacklist.txt')) {
    $blacklist = file($logDir . '/blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
if (in_array($clientIP, $blacklist)) {
    http_response_code(403); // Forbidden
    die("Truy cập bị từ chối do IP của bạn nằm trong danh sách đen.");
}

// **Kiểm tra User-Agent**
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (empty($userAgent) || preg_match('/bot|crawl|spider/i', $userAgent)) {
    logSuspiciousActivity($clientIP, "Yêu cầu không có User-Agent hoặc nghi ngờ là bot.");
    // Có thể thêm CAPTCHA tại đây (xem phần bổ sung dưới)
    // header("Location: /captcha.php");
    // exit();
}

// **Cấu hình cURL an toàn (nếu có sử dụng API)**
function curlSafeRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'TRUYENTRANHNET/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Ngăn chặn redirect độc hại
    curl_setopt($ch, CURLOPT_MAXREDIRS, 0); // Giới hạn số lần redirect
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        logSuspiciousActivity(getClientIP(), "Lỗi cURL: " . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}
?>