<?php
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to repost posts']);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user-id'];
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

    if (!$post_id) {
        die("Invalid post ID");
    }

    // Check if the user has already reposted this post
    $check_sql = "SELECT * FROM reposts WHERE user_id = ? AND post_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $post_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If already reposted, remove the repost (toggle functionality)
        $delete_sql = "DELETE FROM reposts WHERE user_id = ? AND post_id = ?";
        $delete_stmt = $connection->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $user_id, $post_id);
        
        if ($delete_stmt->execute()) {
            echo "Repost removed successfully!";
        } else {
            die("Error removing repost.");
        }
    } else {
        // Insert new repost
        $insert_sql = "INSERT INTO reposts (user_id, post_id) VALUES (?, ?)";
        $insert_stmt = $connection->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $post_id);

        if ($insert_stmt->execute()) {
            echo "Post reposted successfully!";
        } else {
            die("Error reposting the post.");
        }
    }
} else {
    die("Invalid request method.");
}
?>