<?php
require 'partials/header.php';

if (isset($_GET['user'])) {
    $user_id = filter_var($_GET['user'], FILTER_SANITIZE_NUMBER_INT);
    
    // Get user details
    $user_query = "SELECT * FROM users WHERE id=$user_id";
    $user_result = mysqli_query($connection, $user_query);
    $user = mysqli_fetch_assoc($user_result);

    // Get following list with mutual follow status
    $following_query = "SELECT u.*, 
                       CASE WHEN f2.follower_id IS NOT NULL THEN 1 ELSE 0 END as is_following
                       FROM followers f
                       JOIN users u ON f.following_id = u.id
                       LEFT JOIN followers f2 ON f2.following_id = u.id 
                       AND f2.follower_id = {$_SESSION['user-id']}
                       WHERE f.follower_id = $user_id
                       ORDER BY u.firstname ASC";
    $following_result = mysqli_query($connection, $following_query);
}
?>

<section class="following">
    <div class="container followers__container">
        <h2><?= $user['firstname'] . " is Following" ?></h2>
        
        <div class="followers__list">
            <?php if(mysqli_num_rows($following_result) > 0) : ?>
                <?php while($following = mysqli_fetch_assoc($following_result)) : ?>
                    <div class="follower__card">
                        <div class="follower__info">
                            <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $following['id'] ?>" class="follower__avatar">
                                <img src="<?= ROOT_URL . 'images/' . ($following['avatar'] ?? 'default-avatar.png') ?>" 
                                     alt="<?= "{$following['firstname']}'s avatar" ?>">
                            </a>
                            <div class="follower__details">
                                <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $following['id'] ?>">
                                    <h4><?= "{$following['firstname']} {$following['lastname']}" ?></h4>
                                </a>
                                <small><?= $following['bio'] ? substr($following['bio'], 0, 100) . '...' : 'No bio available' ?></small>
                            </div>
                        </div>
                        <?php if($following['id'] !== $_SESSION['user-id']) : ?>
                            <button class="btn follow-btn <?= $following['is_following'] ? 'following' : '' ?>"
                                    data-user-id="<?= $following['id'] ?>"
                                    onclick="toggleFollow(this)">
                                <i class="fas <?= $following['is_following'] ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                                <span><?= $following['is_following'] ? 'Following' : 'Follow Back' ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="alert__message">
                    <p>Not following anyone yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
async function toggleFollow(button) {
    const userId = button.dataset.userId;
    const isFollowing = button.classList.contains('following');
    
    try {
        const response = await fetch('<?= ROOT_URL ?>admin/toggle-follow.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                action: isFollowing ? 'unfollow' : 'follow'
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            button.classList.toggle('following');
            const icon = button.querySelector('i');
            const text = button.querySelector('span');
            
            if (isFollowing) {
                icon.classList.replace('fa-user-minus', 'fa-user-plus');
                text.textContent = 'Follow Back';
            } else {
                icon.classList.replace('fa-user-plus', 'fa-user-minus');
                text.textContent = 'Following';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update follow status');
    }
}
</script>

<?php include '../partials/footer-auth.php'; ?>
