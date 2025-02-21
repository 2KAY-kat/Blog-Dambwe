<?php
require '../config/database.php';

// Prevent any output before headers
ob_clean();
header('Content-Type: application/json');

try {
    // Check login
    if (!isset($_SESSION['user-id'])) {
        throw new Exception('Please sign in to comment');
    }

    // Log incoming data for debugging
    error_log('POST data: ' . print_r($_POST, true));

    // Validate input
    if (empty($_POST['post_id']) || empty($_POST['comment_text'])) {
        throw new Exception('Missing required fields');
    }

    $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
    $comment_text = trim(filter_var($_POST['comment_text'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $user_id = $_SESSION['user-id'];
    $parent_id = !empty($_POST['parent_id']) ? filter_var($_POST['parent_id'], FILTER_SANITIZE_NUMBER_INT) : null;

    // Insert comment
    $query = "INSERT INTO comments (post_id, user_id, comment_text, parent_id, date_time) 
              VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($connection));
    }

    mysqli_stmt_bind_param($stmt, "iisi", $post_id, $user_id, $comment_text, $parent_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to save comment: " . mysqli_stmt_error($stmt));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully'
    ]);

} catch (Exception $e) {
    error_log('Comment error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
