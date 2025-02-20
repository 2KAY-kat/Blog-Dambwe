<div class="post__author">
    <div class="post__author-avatar">
        <img src="<?= ROOT_URL . 'images/' . $post['avatar'] ?>">
    </div>
    <div class="post__author-info">
        <div class="author__header">
            <div>
                <h5><a href="<?= ROOT_URL ?>author_posts.php?id=<?= $post['author_id'] ?>">
                    <?= "{$post['firstname']} {$post['lastname']}" ?>
                </a></h5>
                <small><?= date("M d, Y - H:i", strtotime($post['date_time'])) ?></small>
            </div>
            <?php if(isset($_SESSION['user-id']) && $_SESSION['user-id'] != $post['author_id']): 
                // Check if current user is following this author
                $following_check = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
                $check_stmt = mysqli_prepare($connection, $following_check);
                mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['user-id'], $post['author_id']);
                mysqli_stmt_execute($check_stmt);
                $is_following = mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) > 0;
            ?>
                <button class="follow-btn <?= $is_following ? 'following' : '' ?>" 
                        data-author-id="<?= $post['author_id'] ?>">
                    <i class="uil <?= $is_following ? 'uil-user-check' : 'uil-user-plus' ?>"></i>
                    <span class="follow-text"><?= $is_following ? 'Following' : 'Follow' ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
