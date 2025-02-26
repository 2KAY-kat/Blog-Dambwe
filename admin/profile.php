<?php
include 'partials/header.php';

// Fetch current user's data
$current_user_id = $_SESSION['user-id'];
$query = "SELECT * FROM users WHERE id=$current_user_id";
$result = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($result);
?>

<section class="dashboard">
    <div class="container dashboard__container">
        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-right"></i></button>
        <button id="hide__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-left"></i></button>

        <aside>
            <ul>
                <li><a href="add-post.php"><i class="fas fa-pen"></i>
                    <h5>Add Post</h5>
                </a></li>
                <li><a href="<?= ROOT_URL ?>admin/index.php"><i class="fas fa-pencil"></i>
                    <h5>Manage Posts</h5>
                </a></li>
                <li><a href="edit-profile.php" class="active"><i class="fas fa-user-edit"></i>
                    <h5>Edit Profile</h5>
                </a></li>
                <li><a href="add-category.php"><i class="fas fa-edit"></i>
                    <h5>Add Category</h5>
                </a></li>
                <?php if(isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']): ?>
                    <li><a href="add-user.php"><i class="fas fa-user-plus"></i>
                        <h5>Add User</h5>
                    </a></li>
                    <li><a href="manage-users.php"><i class="fas fa-user-cog"></i>
                        <h5>Manage Users</h5>
                    </a></li>
                    <li><a href="manage-categories.php"><i class="fas fa-list"></i>
                        <h5>Manage Categories</h5>
                    </a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main>
            <?php if(isset($_SESSION['edit-profile-success'])) : ?>
                <div class="alert__message success">
                    <p>
                        <?= $_SESSION['edit-profile-success'];
                        unset($_SESSION['edit-profile-success']);
                        ?>
                    </p>
                </div>
            <?php endif ?>
            
            <div class="profile__container">
                <div class="profile__cover" style="background-image: url('<?= ROOT_URL . 'images/' . ($user['cover_photo'] ?? 'default-cover.jpg') ?>');">
                    <div class="profile__image-container">
                        <div class="profile__image">
                            <img src="<?= ROOT_URL . 'images/' . ($user['avatar'] ?? 'default-avatar.png') ?>" alt="Profile Image">
                        </div>
                        <div class="profile__image-overlay">
                            <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                            <button class="btn" onclick="document.getElementById('avatar-upload').click();">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="profile__info">
                    <h2><?= "{$user['firstname']} {$user['lastname']}" ?></h2>
                    <p class="profile__role"><?= $user['is_admin'] ? 'Administrator' : 'Author' ?></p>
                    <div class="profile__stats">
                        <?php
                            // Get post count
                            $posts_query = "SELECT COUNT(*) as post_count FROM posts WHERE author_id = $current_user_id";
                            $posts_result = mysqli_query($connection, $posts_query);
                            $post_count = mysqli_fetch_assoc($posts_result)['post_count'];

                            // Get followers count
                            $followers_query = "SELECT COUNT(*) as followers FROM followers WHERE following_id = $current_user_id";
                            $followers_result = mysqli_query($connection, $followers_query);
                            $followers_count = mysqli_fetch_assoc($followers_result)['followers'];

                            // Get following count
                            $following_query = "SELECT COUNT(*) as following FROM followers WHERE follower_id = $current_user_id";
                            $following_result = mysqli_query($connection, $following_query);
                            $following_count = mysqli_fetch_assoc($following_result)['following'];
                        ?>
                        <a href="<?= ROOT_URL ?>admin/posts.php?author=<?= $current_user_id ?>" class="stat-link">
                            <span><i class="fas fa-pencil-alt"></i> <?= $post_count ?> Posts</span>
                        </a> |
                        <a href="<?= ROOT_URL ?>admin/followers.php?user=<?= $current_user_id ?>" class="stat-link">
                            <span><i class="fas fa-users"></i> <?= $followers_count ?> Followers</span>
                        </a> |
                        <a href="<?= ROOT_URL ?>admin/following.php?user=<?= $current_user_id ?>" class="stat-link">
                            <span><i class="fas fa-user-friends"></i> <?= $following_count ?> Following</span>
                        </a> |
                        <span><i class="fas fa-calendar"></i> Joined <?= date("M Y", strtotime($user['date_time'])) ?></span>
                    </div>
                </div>
                <div class="profile__content">
                    <h3>Bio</h3>
                    <p><?= $user['bio'] ?? 'No bio available' ?></p>

                    <div class="profile__details">
                        <h3>Account Details</h3>
                        <ul>
                            <li><strong>Email:</strong> <?= $user['email'] ?></li>
                            <li><strong>Joined:</strong> <?= date("M d, Y", strtotime($user['date_time'])) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</section>

<?php
include '../partials/footer-auth.php';
?>

<script>
document.getElementById('avatar-upload').addEventListener('change', async function(e) {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        const formData = new FormData();
        formData.append('avatar', file);
        
        try {
            const response = await fetch('<?= ROOT_URL ?>admin/update-avatar.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Refresh the avatar image
                const avatarImg = document.querySelector('.profile__image img');
                avatarImg.src = data.avatar_url + '?v=' + new Date().getTime();
                // Optional: Show success message
                alert('Profile photo updated successfully!');
            } else {
                alert(data.message || 'Failed to update profile photo');
            }
        } catch (error) {
            alert('An error occurred while updating the profile photo');
            console.error(error);
        }
    }
});
</script>