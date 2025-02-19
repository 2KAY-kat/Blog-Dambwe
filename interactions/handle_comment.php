<?php
require '../config/database.php';
// session_start(); // Remove this line

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment.']);
    exit;
}

$postId = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
$comment = filter_var($_POST['comment'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$userId = $_SESSION['user-id'];
$date_time = date('Y-m-d H:i:s');

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a comment.']);
    exit;
}

// Insert the comment into the database
$query = "INSERT INTO comments (post_id, user_id, comment, date_time) VALUES ($postId, $userId, '$comment', '$date_time')";
$result = mysqli_query($connection, $query);

header('Content-Type: application/json'); // Ensure Content-Type is set
if ($result) {
    echo json_encode(['success' => true, 'post_id' => $postId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to post comment.']);
}
