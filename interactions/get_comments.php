<?php
require '../config/database.php';
require '../helpers/format_time.php';

$postId = filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT);

$query = "SELECT c.*, u.firstname, u.lastname, u.avatar 
          FROM comments c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.post_id = $postId 
          ORDER BY c.date_time DESC";
$result = mysqli_query($connection, $query);

$commentsHtml = '';

if (mysqli_num_rows($result) > 0) {
    while ($comment = mysqli_fetch_assoc($result)) {
        // Get like count for the comment
        $commentId = $comment['id'];
        $likesQuery = "SELECT COUNT(*) AS likes FROM comment_likes WHERE comment_id = $commentId";
        $likesResult = mysqli_query($connection, $likesQuery);
        $likesData = mysqli_fetch_assoc($likesResult);
        $likesCount = $likesData['likes'];

        $commentsHtml .= '<div class="comment">';
        $commentsHtml .= '<div class="comment__author">';
        $commentsHtml .= '<img src="' . ROOT_URL . 'images/' . $comment['avatar'] . '" alt="User Avatar">';
        $commentsHtml .= '<h5>' . $comment['firstname'] . ' ' . $comment['lastname'] . '</h5>';
        $commentsHtml .= '<small>' . timeAgo($comment['date_time']) . '</small>';
        $commentsHtml .= '</div>';
        $commentsHtml .= '<p>' . $comment['comment'] . '</p>';
        $commentsHtml .= '<div class="comment__actions">';
        $commentsHtml .= '<span class="like-comment-btn" data-comment-id="' . $comment['id'] . '"><i class="fa fa-thumbs-up"></i> Like (<span class="like-count">' . $likesCount . '</span>)</span>';
        $commentsHtml .= '</div>';
        $commentsHtml .= '</div>';
    }
} else {
    $commentsHtml = '<p>No comments yet.</p>';
}

echo $commentsHtml;
