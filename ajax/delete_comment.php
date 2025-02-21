<?php
require '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to delete comments']);
    exit;
}

if (!isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit;
}

$comment_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_NUMBER_INT);
$user_id = $_SESSION['user-id'];

try {
    // First verify the comment belongs to the user
    $verify_query = "SELECT user_id FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($connection, $verify_query);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comment = mysqli_fetch_assoc($result);

    if (!$comment || $comment['user_id'] != $user_id) {
        throw new Exception('You are not authorized to delete this comment');
    }

    // Start transaction
    mysqli_begin_transaction($connection);

    // Delete all child comments first
    $delete_children = "DELETE FROM comments WHERE parent_id = ?";
    $stmt = mysqli_prepare($connection, $delete_children);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);

    // Delete the comment itself
    $delete_query = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
    mysqli_stmt_execute($stmt);

    // Commit transaction
    mysqli_commit($connection);

    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);

} catch (Exception $e) {
    mysqli_rollback($connection);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
