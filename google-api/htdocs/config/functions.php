<?php
// functions.php

// Hàm chuyển đổi trạng thái
function translateStatus($status) {
    switch ($status) {
        case 'ongoing':
            return 'Đang Phát Hành';
        case 'completed':
            return 'Hoàn Thành';
        case 'coming_soon':
            return 'Sắp Ra Mắt';
        default:
            return 'Không Xác Định';
    }
}
?>