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
                <div class="profile__header">
                    <div class="profile__image">
                        <img src="<?= ROOT_URL . 'images/' . $user['avatar'] ?>" alt="Profile Image">
                    </div>
                    <div class="profile__info">
                        <h2><?= "{$user['firstname']} {$user['lastname']}" ?></h2>
                        <p class="profile__role"><?= $user['is_admin'] ? 'Administrator' : 'Author' ?></p>
                    </div>
                    <a href="<?= ROOT_URL ?>admin/edit-profile.php" class="btn">Edit Profile</a>
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
