<?php
require 'config/database.php';

header('Content-Type: application/json'); // Add JSON header

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to repost posts']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        mysqli_begin_transaction($connection);
        
        $user_id = $_SESSION['user-id'];
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$post_id) {
            throw new Exception("Invalid post ID");
        }

        // Get post details for notification
        $post_query = "SELECT p.title, p.author_id FROM posts p WHERE p.id = ?";
        $post_stmt = mysqli_prepare($connection, $post_query);
        mysqli_stmt_bind_param($post_stmt, "i", $post_id);
        mysqli_stmt_execute($post_stmt);
        $post_info = mysqli_fetch_assoc(mysqli_stmt_get_result($post_stmt));

        if (!$post_info) {
            throw new Exception("Post not found");
        }

        // Check if already reposted
        $check_sql = "SELECT id FROM reposts WHERE user_id = ? AND post_id = ?";
        $check_stmt = mysqli_prepare($connection, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $post_id);
        mysqli_stmt_execute($check_stmt);
        $exists = mysqli_stmt_get_result($check_stmt)->num_rows > 0;

        if ($exists) {
            // Remove repost
            $delete_sql = "DELETE FROM reposts WHERE user_id = ? AND post_id = ?";
            $stmt = mysqli_prepare($connection, $delete_sql);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error removing repost");
            }

            $response = ['success' => true, 'message' => 'Repost removed successfully', 'action' => 'removed'];
        } else {
            // Add new repost
            $insert_sql = "INSERT INTO reposts (user_id, post_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($connection, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error adding repost");
            }

            // Create notification if reposting someone else's post
            if ($post_info['author_id'] != $user_id) {
                $notification_sql = "INSERT INTO notifications (recipient_id, sender_id, post_id, type, message) 
                                   VALUES (?, ?, ?, 'repost', ?)";
                $notification_stmt = mysqli_prepare($connection, $notification_sql);
                $message = "reposted your post \"" . $post_info['title'] . "\"";
                
                mysqli_stmt_bind_param($notification_stmt, "iiis", 
                    $post_info['author_id'],
                    $user_id,
                    $post_id,
                    $message
                );

                if (!mysqli_stmt_execute($notification_stmt)) {
                    throw new Exception("Error creating notification");
                }
            }

            $response = ['success' => true, 'message' => 'Post reposted successfully', 'action' => 'added'];
        }

        mysqli_commit($connection);
        echo json_encode($response);

    } catch (Exception $e) {
        mysqli_rollback($connection);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>