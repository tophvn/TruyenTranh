<?php
include('../config/database.php');

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$truyenId = isset($_POST['truyen_id']) ? intval($_POST['truyen_id']) : 0;

if ($userId && $truyenId) {
    $checkQuery = "SELECT * FROM theodoi WHERE user_id = ? AND truyen_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $userId, $truyenId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO theodoi (user_id, truyen_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ii", $userId, $truyenId);
        $insertStmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Truyện đã có trong danh sách theo dõi!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết.']);
}
?>