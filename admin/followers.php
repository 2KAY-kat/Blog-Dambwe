<?php
require 'partials/header.php';

if (isset($_GET['user'])) {
    $user_id = filter_var($_GET['user'], FILTER_SANITIZE_NUMBER_INT);
    
    // Get user details
    $user_query = "SELECT * FROM users WHERE id=$user_id";
    $user_result = mysqli_query($connection, $user_query);
    $user = mysqli_fetch_assoc($user_result);

    // Get followers
    $followers_query = "SELECT u.*, 
                       CASE WHEN f2.follower_id IS NOT NULL THEN 1 ELSE 0 END as is_following
                       FROM followers f
                       JOIN users u ON f.follower_id = u.id
                       LEFT JOIN followers f2 ON f2.following_id = u.id 
                       AND f2.follower_id = {$_SESSION['user-id']}
                       WHERE f.following_id = $user_id
                       ORDER BY u.firstname ASC";
    $followers_result = mysqli_query($connection, $followers_query);
}
?>

<section class="followers">
    <div class="container followers__container">
        <h2><?= $user['firstname'] . "'s Followers" ?></h2>
        
        <div class="followers__list">
            <?php if(mysqli_num_rows($followers_result) > 0) : ?>
                <?php while($follower = mysqli_fetch_assoc($followers_result)) : ?>
                    <div class="follower__card">
                        <div class="follower__info">
                            <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $follower['id'] ?>" class="follower__avatar">
                                <img src="<?= ROOT_URL . 'images/' . ($follower['avatar'] ?? 'default-avatar.png') ?>" alt="Follower Avatar">
                            </a>
                            <div class="follower__details">
                                <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $follower['id'] ?>">
                                    <h4><?= "{$follower['firstname']} {$follower['lastname']}" ?></h4>
                                </a>
                                <small><?= $follower['bio'] ? substr($follower['bio'], 0, 100) . '...' : 'No bio available' ?></small>
                            </div>
                        </div>
                        <?php if($follower['id'] !== $_SESSION['user-id']) : ?>
                            <button class="btn follow-btn <?= $follower['is_following'] ? 'following' : '' ?>"
                                    data-user-id="<?= $follower['id'] ?>"
                                    onclick="toggleFollow(this)">
                                <i class="fas <?= $follower['is_following'] ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                                <span><?= $follower['is_following'] ? 'Unfollow' : 'Follow' ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="alert__message">
                    <p>No followers yet</p>
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
                text.textContent = 'Follow';
            } else {
                icon.classList.replace('fa-user-plus', 'fa-user-minus');
                text.textContent = 'Unfollow';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update follow status');
    }
}
</script>

<?php include '../partials/footer.php'; ?>
