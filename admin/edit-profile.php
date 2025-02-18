<?php
require '../config/database.php';  // Include database first
include 'partials/header.php';    // Then include header

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
                <h2>Edit Profile</h2>
                <?php if(isset($_SESSION['edit-profile'])) : ?>
                    <div class="alert__message error">
                        <p>
                            <?= $_SESSION['edit-profile'];
                            unset($_SESSION['edit-profile']);
                            ?>
                        </p>
                    </div>
                <?php endif ?>
                <form class="form" action="<?= ROOT_URL ?>admin/edit-profile-logic.php" enctype="multipart/form-data" method="POST">
                    <div class="form__control">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" value="<?= $user['firstname'] ?>" placeholder="First Name">
                    </div>
                    <div class="form__control">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" value="<?= $user['lastname'] ?>" placeholder="Last Name">
                    </div>
                    <div class="form__control">
                        <label for="bio">Bio</label>
                        <textarea rows="5" id="bio" name="bio" placeholder="Write your bio here..."><?= $user['bio'] ?? '' ?></textarea>
                    </div>
                    <div class="form__control">
                        <label for="avatar">Change Avatar</label>
                        <div class="avatar-preview">
                            <img id="avatar-preview" src="<?= ROOT_URL . 'images/' . ($user['avatar'] ?: 'default-avatar.png') ?>" alt="Avatar Preview">
                        </div>
                        <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <button type="submit" name="submit" class="btn">Update Profile</button>
                </form>
            </div>
        </main>
   
</section>

<script>
function previewImage(input) {
    const preview = document.getElementById('avatar-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
include '../partials/footer-auth.php';
?>