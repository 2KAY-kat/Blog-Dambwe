<?php
require '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user-id'])) {
        throw new Exception('Please sign in to like posts');
    }

    $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
    $action = $_POST['action'];
    $user_id = $_SESSION['user-id'];
    
    // Determine like value based on action
    $like_value = ($action === 'like') ? 1 : -1;

    // Check if user has already liked/disliked
    $check_query = "SELECT * FROM likes_dislikes WHERE user_id = ? AND post_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $post_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        $existing = mysqli_fetch_assoc($result);
        if ($existing['like_value'] == $like_value) {
            // Remove like/dislike if clicking same button
            $query = "DELETE FROM likes_dislikes WHERE user_id = ? AND post_id = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
            mysqli_stmt_execute($stmt);
        } else {
            // Update existing record
            $query = "UPDATE likes_dislikes SET like_value = ? WHERE user_id = ? AND post_id = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "iii", $like_value, $user_id, $post_id);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // Insert new like/dislike
        $query = "INSERT INTO likes_dislikes (user_id, post_id, like_value) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $post_id, $like_value);
        mysqli_stmt_execute($stmt);
    }

    // Get updated counts
    $likes_query = "SELECT COUNT(*) as likes FROM likes_dislikes WHERE post_id = ? AND like_value = 1";
    $dislikes_query = "SELECT COUNT(*) as dislikes FROM likes_dislikes WHERE post_id = ? AND like_value = -1";
    
    $likes_stmt = mysqli_prepare($connection, $likes_query);
    mysqli_stmt_bind_param($likes_stmt, "i", $post_id);
    mysqli_stmt_execute($likes_stmt);
    $likes = mysqli_fetch_assoc(mysqli_stmt_get_result($likes_stmt))['likes'];

    $dislikes_stmt = mysqli_prepare($connection, $dislikes_query);
    mysqli_stmt_bind_param($dislikes_stmt, "i", $post_id);
    mysqli_stmt_execute($dislikes_stmt);
    $dislikes = mysqli_fetch_assoc(mysqli_stmt_get_result($dislikes_stmt))['dislikes'];

    echo json_encode([
        'success' => true,
        'likes' => $likes,
        'dislikes' => $dislikes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
