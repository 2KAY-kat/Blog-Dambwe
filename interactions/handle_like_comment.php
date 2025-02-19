<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like comments.']);
    exit;
}

$commentId = filter_var($_POST['comment_id'], FILTER_SANITIZE_NUMBER_INT);
$userId = $_SESSION['user-id'];

// Check if the user has already liked the comment
$checkQuery = "SELECT * FROM comment_likes WHERE user_id = $userId AND comment_id = $commentId";
$checkResult = mysqli_query($connection, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    // User has already liked, so unlike
    $deleteQuery = "DELETE FROM comment_likes WHERE user_id = $userId AND comment_id = $commentId";
    mysqli_query($connection, $deleteQuery);
} else {
    // User is liking the comment
    $insertQuery = "INSERT INTO comment_likes (user_id, comment_id) VALUES ($userId, $commentId)";
    mysqli_query($connection, $insertQuery);
}

// Get updated like count
$likesQuery = "SELECT COUNT(*) AS likes FROM comment_likes WHERE comment_id = $commentId";
$likesResult = mysqli_query($connection, $likesQuery);
$likesData = mysqli_fetch_assoc($likesResult);

echo json_encode(['success' => true, 'likes' => $likesData['likes']]);
