<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$follower_id = $_SESSION['user-id'];
$following_id = filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);
$action = $data['action'];

if ($action === 'follow') {
    $query = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
} else {
    $query = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
}

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "ii", $follower_id, $following_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
