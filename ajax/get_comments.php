<?php
require '../config/database.php';
require '../helpers/format_time.php';

// Prevent any output before our response
ob_clean();

// First, fix any NULL parent_ids that should be 0
$fix_query = "UPDATE comments SET parent_id = 0 WHERE parent_id IS NULL";
mysqli_query($connection, $fix_query);

function get_comments_tree($post_id, $parent_id = 0, $level = 0) {
    global $connection;
    
    // Updated query to handle both NULL and 0 as valid top-level comments
    $query = "SELECT c.*, u.firstname, u.lastname, u.avatar,
              (SELECT COUNT(*) FROM comments WHERE parent_id = c.id) as reply_count
              FROM comments c
              JOIN users u ON c.user_id = u.id
              WHERE c.post_id = ? AND (c.parent_id = ? OR (c.parent_id IS NULL AND ? = 0))
              ORDER BY c.date_time DESC";
    
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "iii", $post_id, $parent_id, $parent_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Debug log
    error_log("Fetching comments for post_id: $post_id, parent_id: $parent_id, found: " . mysqli_num_rows($result));
    
    $html = '';
    while ($comment = mysqli_fetch_assoc($result)) {
        // Add debug info as HTML comment
        $html .= "<!-- Comment ID: {$comment['id']}, Parent ID: {$comment['parent_id']} -->\n";
        
        $html .= '<div class="comment level-' . $level . '">
            <div class="comment-content">
                <div class="comment-avatar">
                    <img src="' . ROOT_URL . 'images/' . htmlspecialchars($comment['avatar']) . '" alt="">
                </div>
                <div class="comment-body">
                    <h4>' . htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) . '</h4>
                    <p>' . htmlspecialchars($comment['comment_text']) . '</p>
                    <div class="comment-meta">
                        <small>' . timeAgo($comment['date_time']) . '</small>
                        ' . (isset($_SESSION['user-id']) ? 
                            '<button class="reply-btn" data-comment-id="' . $comment['id'] . '">
                                <i class="fas fa-reply"></i> Reply
                            </button>' : '') . '
                    </div>
                </div>
            </div>';
        
        if ($comment['reply_count'] > 0) {
            $html .= '<div class="comment-replies">';
            $html .= get_comments_tree($post_id, $comment['id'], $level + 1);
            $html .= '</div>';
            $html .= '<button class="toggle-replies">Show Replies (' . $comment['reply_count'] . ')</button>';
        }
        
        $html .= '</div>';
    }
    
    return $html;
}

try {
    if (!isset($_GET['post_id'])) {
        throw new Exception('Post ID is required');
    }

    $post_id = filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Debug total comment count
    $count_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = ?";
    $count_stmt = mysqli_prepare($connection, $count_query);
    mysqli_stmt_bind_param($count_stmt, "i", $post_id);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_comments = mysqli_fetch_assoc($count_result)['total'];
    
    error_log("Total comments for post $post_id: $total_comments");
    
    // Get comments tree
    $comments_html = get_comments_tree($post_id);
    
    if (empty($comments_html)) {
        echo '<div class="no-comments">No comments yet. Be the first to comment!</div>';
    } else {
        echo $comments_html;
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
}
