<?php
function get_comments($post_id, $parent_id = null) {
    global $connection;
    
    // Modify the query to select specific fields and use proper table aliases
    $sql = "SELECT c.*, u.firstname, u.lastname, u.avatar 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? AND c.parent_id ";
    
    if ($parent_id === null) {
        $sql .= "IS NULL ORDER BY c.date_time DESC";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
    } else {
        $sql .= "= ? ORDER BY c.date_time ASC";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $post_id, $parent_id);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $comments = [];
    while ($comment = mysqli_fetch_assoc($result)) {
        $comment['replies'] = get_comments($post_id, $comment['id']);
        $comments[] = $comment;
    }
    
    return $comments;
}

function display_comments($comments, $level = 0) {
    if (!is_array($comments)) return;
    
    foreach ($comments as $comment) {
        if (!isset($comment['comment_text']) && !isset($comment['firstname'])) continue;
        ?>
        <div class="comment" style="margin-left: <?= $level * 2 ?>rem">
            <div class="comment-avatar">
                <img src="<?= ROOT_URL . 'images/' . htmlspecialchars($comment['avatar']) ?>" alt="">
            </div>
            <div class="comment-content">
                <h4><?= htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) ?></h4>
                <p><?= htmlspecialchars($comment['comment_text'] ?? '') ?></p>
                <small><?= date('M d, Y - H:i', strtotime($comment['date_time'])) ?></small>
                <?php if(isset($_SESSION['user-id'])): ?>
                    <button class="reply-btn" data-comment-id="<?= $comment['id'] ?>">Reply</button>
                <?php endif; ?>
            </div>
        </div>
        <?php 
        if (!empty($comment['replies'])) {
            display_comments($comment['replies'], $level + 1);
        }
    }
}
?>
