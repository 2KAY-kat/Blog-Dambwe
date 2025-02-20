<?php
include 'partials/header.php';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM users WHERE id=$id";
    $result = mysqli_query($connection, $query);
    $user = mysqli_fetch_assoc($result);
} else {
    header('location: ' . ROOT_URL . 'admin/manage-users.php');
    die();
}
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
                <li><a href="index.php"><i class="fas fa-pencil"></i>
                    <h5>Manage Posts</h5>
                </a></li>
                <?php if(isset($_SESSION['user_is_admin'])): ?>
                    <li><a href="add-user.php"><i class="fas fa-user-plus"></i>
                        <h5>Add User</h5>
                    </a></li>
                    <li><a href="manage-users.php" class="active"><i class="fas fa-user-cog"></i>
                        <h5>Manage Users</h5>
                    </a></li>
                    <li><a href="add-category.php"><i class="fas fa-edit"></i>
                        <h5>Add Category</h5>
                    </a></li>
                    <li><a href="manage-categories.php"><i class="fas fa-list"></i>
                        <h5>Manage Categories</h5>
                    </a></li>
                <?php endif ?>
            </ul>
        </aside>

        <main>
            <h2>Edit User</h2>
            <?php if(isset($_SESSION['edit-user'])): ?>
                <div class="alert__message error">
                    <p>
                        <?= $_SESSION['edit-user'];
                        unset($_SESSION['edit-user']);
                        ?>
                    </p>
                </div>
            <?php endif ?>

            <form class="form" action="<?= ROOT_URL ?>admin/edit-user-logic.php" method="POST">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <div class="form__control">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?= $user['firstname'] ?>">
                </div>
                <div class="form__control">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?= $user['lastname'] ?>">
                </div>
                <?php if(isset($_SESSION['user_is_admin'])): ?>
                <div class="form__control">
                    <label>User Role</label>
                    <div class="radio__group">
                        <div class="radio__option">
                            <input type="radio" id="author" name="userrole" value="0" <?= $user['is_admin'] ? '' : 'checked' ?>>
                            <label for="author">Author</label>
                        </div>
                        <div class="radio__option">
                            <input type="radio" id="admin" name="userrole" value="1" <?= $user['is_admin'] ? 'checked' : '' ?>>
                            <label for="admin">Admin</label>
                        </div>
                    </div>
                </div>
                <?php endif ?>
                <button type="submit" name="submit" class="btn">Update User</button>
            </form>
        </main>
    </div>
</section>

<?php
include '../partials/footer-auth.php';
?>

