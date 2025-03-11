<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user-id'];
$query = "SELECT COUNT(*) as count FROM notifications WHERE recipient_id = ? AND is_read = 0";
$stmt = mysqli_prepare($connection, $query);

if ($stmt === false) {
    echo json_encode(['error' => 'Database error', 'count' => 0]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$count = mysqli_fetch_assoc($result)['count'];

echo json_encode(['count' => $count]);

mysqli_stmt_close($stmt);
