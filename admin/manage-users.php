<?php
include 'partials/header.php';

// Redirect non-admin users
if(!$_SESSION['user_is_admin']) {
    header('location: ' . ROOT_URL . 'admin/');
    die();
}

// fetch all users from db except current

$current_admin_id = $_SESSION['user-id'];

$query = "SELECT * FROM users WHERE NOT id=$current_admin_id";
$users = mysqli_query($connection, $query);

?>

<section class="dashboard">


<?php if(isset($_SESSION['add-user-success'])) : //User adding was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['add-user-success'];
                unset($_SESSION['add-user-success']);
                ?>
            </p>
        </div>

        <?php elseif(isset($_SESSION['edit-user-success'])) : //User editing was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['edit-user-success'];
                unset($_SESSION['edit-user-success']);
                ?>
            </p>
        </div>
        <?php elseif(isset($_SESSION['edit-user'])) : //User editing was not successful ?>
        <div class="alert__message error container">
            <p>
                <?= $_SESSION['edit-user'];
                unset($_SESSION['edit-user']);
                ?>
            </p>
        </div>

        <?php elseif(isset($_SESSION['delete-user'])) : //User deleting was not successful ?>
        <div class="alert__message error container">
            <p>
                <?= $_SESSION['delete-user'];
                unset($_SESSION['delete-user']);
                ?>
            </p>
        </div>
        <?php elseif(isset($_SESSION['delete-user-success'])) : //User deleting was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['delete-user-success'];
                unset($_SESSION['delete-user-success']);
                ?>
            </p>
        </div>

        <?php endif ?>

    <div class="container dashboard__container">


        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-right"></i></button>
        <button id="hide__sidebar-btn"class="sidebar__toggle"><i class="fas fa-chevron-left"></i></button>

        <aside>
            <ul>
                <li><a href="add-post.php"><i class="fas fa-pen"></i>
                    <h5>Add Post</h5>
                </a></li>

                <li><a href="<?= ROOT_URL ?>admin/index.php"><i class="fas fa-pencil"></i>
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
                    <h5>Manage Category</h5>
                </a></li>

                <?php endif ?>
            </ul>
        </aside>
        <main>
            <h2>Manage Users</h2>

            <?php if(mysqli_num_rows($users) > 0) : ?>

            <table>
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Edit</th>
                        <th>Delete</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users)) : ?>
                    <tr>
                        <td>
                            <div class="avatar">
                                <img src="<?= ROOT_URL . 'images/' . $user['avatar'] ?>" alt="<?= "{$user['firstname']} {$user['lastname']}'s avatar" ?>">
                            </div>
                        </td>
                        <td><?= "{$user['firstname']} {$user['lastname']}" ?></td>
                        <td><?= $user['username'] ?></td>
                        <td><a href="<?= ROOT_URL ?>admin/edit-user.php?id=<?= $user['id'] ?>" class="btn sm">Edit</a></td>
                        <td><a href="<?= ROOT_URL ?>admin/delete-user.php?id=<?= $user['id'] ?>" class="btn sm danger">Delete</a></td>
                        <td><?= $user['is_admin'] ? 'YES' : 'NO' ?></td>
                    </tr>
                 <?php endwhile ?>
                </tbody>
            </table>

            <?php else : ?>
                <div class="alert__message error"><?= "No Users Found" ?></div>
                <?php endif ?>
        </main>
    </div>
</section>


<?php
include '../partials/footer-auth.php';
?>