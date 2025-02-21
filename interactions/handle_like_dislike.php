<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to like or dislike posts']);
    exit;
}

$user_id = $_SESSION['user-id'];
$post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

// Determine the like_value based on action
$like_value = ($action === 'like') ? 1 : -1;

// Check if user has already liked/disliked
$check_query = "SELECT * FROM likes_dislikes WHERE user_id = $user_id AND post_id = $post_id";
$check_result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $existing_like = mysqli_fetch_assoc($check_result);
    if ($existing_like['like_value'] == $like_value) {
        // User is un-liking/un-disliking
        $query = "DELETE FROM likes_dislikes WHERE user_id = $user_id AND post_id = $post_id";
        $like_value = 0;
    } else {
        // User is changing their vote
        $query = "UPDATE likes_dislikes SET like_value = $like_value WHERE user_id = $user_id AND post_id = $post_id";
    }
} else {
    // New like/dislike
    $query = "INSERT INTO likes_dislikes (user_id, post_id, like_value) VALUES ($user_id, $post_id, $like_value)";
}

mysqli_query($connection, $query);

// Get updated counts
$likes_query = "SELECT COUNT(*) as count FROM likes_dislikes WHERE post_id = $post_id AND like_value = 1";
$dislikes_query = "SELECT COUNT(*) as count FROM likes_dislikes WHERE post_id = $post_id AND like_value = -1";

$likes_result = mysqli_query($connection, $likes_query);
$dislikes_result = mysqli_query($connection, $dislikes_query);

$likes = mysqli_fetch_assoc($likes_result)['count'];
$dislikes = mysqli_fetch_assoc($dislikes_result)['count'];

echo json_encode([
    'success' => true,
    'likes' => $likes,
    'dislikes' => $dislikes,
    'user_like_value' => $like_value
]);
