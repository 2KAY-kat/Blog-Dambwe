<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['error' => 'Please login to follow']);
    exit;
}

if (isset($_POST['author_id'])) {
    $follower_id = $_SESSION['user-id'];
    $author_id = filter_var($_POST['author_id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Check if not trying to follow self
    if ($follower_id == $author_id) {
        echo json_encode(['error' => 'You cannot follow yourself']);
        exit;
    }

    // Check if already following
    $check_query = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $follower_id, $author_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        // Unfollow
        $query = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        $action = 'unfollow';
    } else {
        // Follow
        $query = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
        $action = 'follow';
    }

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $follower_id, $author_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get updated follower count
        $count_query = "SELECT COUNT(*) as count FROM followers WHERE following_id = ?";
        $count_stmt = mysqli_prepare($connection, $count_query);
        mysqli_stmt_bind_param($count_stmt, "i", $author_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $follower_count = mysqli_fetch_assoc($count_result)['count'];

        echo json_encode([
            'status' => 'success',
            'action' => $action,
            'count' => $follower_count
        ]);
    } else {
        echo json_encode(['error' => 'Database error']);
    }
}
