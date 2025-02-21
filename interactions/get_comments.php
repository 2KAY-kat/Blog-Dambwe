<?php
require '../config/database.php';
require '../helpers/format_time.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['post_id'])) {
        throw new Exception('Post ID is required');
    }

    $post_id = filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT);
    
    $query = "SELECT c.*, u.firstname, u.lastname, u.avatar, 
              (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count
              FROM comments c
              JOIN users u ON c.user_id = u.id
              WHERE c.post_id = ?
              ORDER BY c.date_time DESC";
              
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = [
            'id' => $row['id'],
            'text' => $row['comment_text'],
            'author' => $row['firstname'] . ' ' . $row['lastname'],
            'avatar' => $row['avatar'],
            'date' => timeAgo($row['date_time']),
            'likes' => $row['like_count'],
            'user_id' => $row['user_id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
