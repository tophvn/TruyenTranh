<?php
require __DIR__ .'/../PHPMailer/src/PHPMailer.php';
require __DIR__ .'/../PHPMailer/src/SMTP.php';
require __DIR__ .'/../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$common_css = "
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #2C3E50;
            text-align: center;
        }
        p {
            font-size: 16px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table th {
            background-color: #f4f4f4;
            color: #333;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
        .footer p {
            margin: 5px 0;
        }
        .button-container {
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            font-size: 18px;
            text-decoration: none;
            border-radius: 5px;
            background-color: #ffb6c1;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #218838;
        }
    </style>
";


// Hàm gửi email chứa mã OTP
function send_otp_email($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0; 
        $mail->isSMTP();
        $mail->CharSet = "utf-8";
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'truyentranhnetcontact@gmail.com'; 
        $mail->Password = 'juuf bzoq eysl zdag'; 
        $mail->SMTPSecure = 'ssl';
        $mail->Port=465;
        $mail->setFrom('truyentranhnetcontact@gmail.com', 'TRUYENTRANHNET');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'XÁC THỰC TÀI KHOẢN';
        $mail->Body = "Mã OTP của bạn là: <strong>$otp</strong>";
        $mail->send();
    } catch (Exception $e) {
        echo 'Lỗi khi gửi email: ' . $mail->ErrorInfo;
    }
}

// // Hàm gửi email thông báo đăng nhập
// function send_login_notification($email, $ip_address, $user_agent) {
//     global $common_css; 
//     $mail = new PHPMailer(true);
//     try {
//         $mail->SMTPDebug = 0; 
//         $mail->isSMTP();
//         $mail->CharSet = "utf-8";
//         $mail->Host = 'smtp.gmail.com';
//         $mail->SMTPAuth = true;
//         $mail->Username = 'hplfashionvn@gmail.com';
//         $mail->Password = 'nwzh iggi lvum dlqb'; 
//         $mail->SMTPSecure = 'ssl';
//         $mail->Port = 465;
//         $mail->setFrom('hplfashionvn@gmail.com', 'HPL - Fashion');
//         $mail->addAddress($email);
//         $mail->isHTML(true);
//         $mail->Subject = 'Thông Báo Đăng Nhập';
//         $mail->Body = "
//         <html>
//         <head>
//             $common_css
//         </head>
//         <body>
//             <div class='container'>
//                 <p>Bạn vừa đăng nhập vào tài khoản của mình.</p>
//                 <p>Địa chỉ IP: $ip_address</p>
//                 <p>User Agent: $user_agent</p>
//                 <p>Nếu bạn không nhận ra đăng nhập này, vui lòng thay đổi mật khẩu ngay lập tức để bảo vệ tài khoản của bạn.</p>
//                 <p>Trân trọng,</p>
//                 <p>HPL - Fashion</p>
//         </div>
//         </body>
//         </html>";
//         $mail->send();
//     } catch (Exception $e) {
//         echo 'Lỗi khi gửi email: ' . $mail->ErrorInfo;
//     }
// }

// Hàm gửi email đặt lại mật khẩu
function send_password_reset_email($email, $token) {
    global $common_css;
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->CharSet = "utf-8";
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'truyentranhnetcontact@gmail.com';
        $mail->Password = 'juuf bzoq eysl zdag'; 
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('truyentranhnetcontact@gmail.com', 'TRUYENTRANHNET');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Đặt Lại Mật Khẩu';
        $resetLink = "https://truyentranhnet.free.nf/views/reset_password.php?token=" . $token;
        $mail->Body = "
            <html>
            <head>
                $common_css
            </head>
            <body>
                <div class='container'>
                    <h2>Đặt Lại Mật Khẩu</h2>
                    <p>Vui lòng nhấp vào nút dưới đây để đặt lại mật khẩu của bạn:</p>
                    <div class='button-container'>
                        <a href='$resetLink' class='button'>Đặt Lại Mật Khẩu</a>
                    </div>
                    <p>Nếu bạn không yêu cầu thay đổi mật khẩu, vui lòng bỏ qua email này.</p>
                </div>
                <div class='footer'>
                    <p>Trân trọng,</p>
                    <p>TRUYENTRANHNET</p>
                </div>
            </body>
            </html>
        ";
        $mail->send();
    } catch (Exception $e) {
        echo 'Lỗi khi gửi email: ' .$mail->ErrorInfo;
    }
}