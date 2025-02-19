<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like/dislike.']);
    exit;
}

$postId = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
$userId = $_SESSION['user-id'];

// Check if the user has already liked/disliked the post
$checkQuery = "SELECT * FROM likes_dislikes WHERE user_id = $userId AND post_id = $postId";
$checkResult = mysqli_query($connection, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $existingLike = mysqli_fetch_assoc($checkResult);
    // User has already interacted
    if (($action == 'like' && $existingLike['like_value'] == 1) || ($action == 'dislike' && $existingLike['like_value'] == -1)) {
        // User is undoing their action
        $deleteQuery = "DELETE FROM likes_dislikes WHERE id = " . $existingLike['id'];
        mysqli_query($connection, $deleteQuery);
    } else {
        // User is changing their action
        $newValue = ($action == 'like') ? 1 : -1;
        $updateQuery = "UPDATE likes_dislikes SET like_value = $newValue WHERE id = " . $existingLike['id'];
        mysqli_query($connection, $updateQuery);
    }
} else {
    // User is interacting for the first time
    $likeValue = ($action == 'like') ? 1 : -1;
    $insertQuery = "INSERT INTO likes_dislikes (user_id, post_id, like_value) VALUES ($userId, $postId, $likeValue)";
    mysqli_query($connection, $insertQuery);
}

// Get updated like/dislike counts
$likesQuery = "SELECT COUNT(*) AS likes FROM likes_dislikes WHERE post_id = $postId AND like_value = 1";
$dislikesQuery = "SELECT COUNT(*) AS dislikes FROM likes_dislikes WHERE post_id = $postId AND like_value = -1";

$likesResult = mysqli_query($connection, $likesQuery);
$dislikesResult = mysqli_query($connection, $dislikesQuery);

$likesData = mysqli_fetch_assoc($likesResult);
$dislikesData = mysqli_fetch_assoc($dislikesResult);

// Get the user's current like value
$userLikeValueQuery = "SELECT like_value FROM likes_dislikes WHERE post_id = $postId AND user_id = $userId";
$userLikeValueResult = mysqli_query($connection, $userLikeValueQuery);

if (mysqli_num_rows($userLikeValueResult) > 0) {
    $userLikeValueData = mysqli_fetch_assoc($userLikeValueResult);
    $userLikeValue = $userLikeValueData['like_value'];
} else {
    $userLikeValue = 0; // No like/dislike
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'likes' => $likesData['likes'],
    'dislikes' => $dislikesData['dislikes'],
    'user_like_value' => $userLikeValue
]);
