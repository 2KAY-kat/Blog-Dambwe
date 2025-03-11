<?php
require 'partials/header.php';

if(!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

$user_id = $_SESSION['user-id'];

// Mark notifications as read
if(isset($_GET['mark_read'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE recipient_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    if($stmt === false) {
        die("Error preparing statement: " . mysqli_error($connection));
    }
    if(!mysqli_stmt_bind_param($stmt, "i", $user_id)) {
        die("Error binding parameters: " . mysqli_stmt_error($stmt));
    }
    if(!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

// Get all notifications for user
$query = "SELECT n.*, 
          p.title as post_title, 
          u.firstname as sender_firstname, 
          u.lastname as sender_lastname,
          u.avatar as sender_avatar,
          c.comment_text as comment_body,
          c.date_time as comment_date
          FROM notifications n
          LEFT JOIN posts p ON n.post_id = p.id 
          LEFT JOIN users u ON n.sender_id = u.id
          LEFT JOIN comments c ON n.comment_id = c.id
          WHERE n.recipient_id = ? 
          ORDER BY n.created_at DESC";

$stmt = mysqli_prepare($connection, $query);
if($stmt === false) {
    die("Error preparing statement: " . mysqli_error($connection));
}

if(!mysqli_stmt_bind_param($stmt, "i", $user_id)) {
    die("Error binding parameters: " . mysqli_stmt_error($stmt));
}

if(!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if($result === false) {
    die("Error getting results: " . mysqli_stmt_error($stmt));
}
?>

<section class="dashboard">
    <div class="container dashboard__container">
        <h2>Notifications</h2>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="notifications__header">
                <a href="?mark_read=1" class="btn mark-read">Mark all as read</a>
            </div>
            
            <div class="notifications__list">
                <?php while($notification = mysqli_fetch_assoc($result)): ?>
                    <div class="notification <?= $notification['is_read'] ? 'read' : 'unread' ?>">
                        <div class="notification__avatar">
                            <img src="<?= ROOT_URL . 'images/' . ($notification['sender_avatar'] ?? 'default-avatar.png') ?>" 
                                 alt="<?= htmlspecialchars($notification['sender_firstname'] . ' ' . $notification['sender_lastname']) ?>">
                        </div>
                        <div class="notification__info">
                            <p>
                                <span class="reactor-name">
                                    <?= htmlspecialchars($notification['sender_firstname'] . ' ' . $notification['sender_lastname']) ?>
                                </span>
                                <?= htmlspecialchars($notification['message']) ?>
                                <?php if($notification['type'] === 'comment' || $notification['type'] === 'reply'): ?>
                                    <span class="comment-preview">
                                        "<?= htmlspecialchars(substr($notification['comment_body'], 0, 50)) ?>..."
                                    </span>
                                <?php endif; ?>
                                <?php if($notification['post_title']): ?>
                                    <span class="post-title">
                                        on "<a href="<?= ROOT_URL ?>post.php?id=<?= $notification['post_id'] ?><?= 
                                            $notification['comment_id'] ? '#comment-' . $notification['comment_id'] : '' 
                                        ?>"><?= htmlspecialchars($notification['post_title']) ?></a>"
                                    </span>
                                <?php endif; ?>
                            </p>
                            <small><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="empty">No notifications yet</p>
        <?php endif; ?>
    </div>
</section>

<?php 
mysqli_stmt_close($stmt);
include '../partials/footer.php'; 
?>
