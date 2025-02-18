<?php
include 'partials/header.php';

// Redirect non-admin users
if(!$_SESSION['user_is_admin']) {
    header('location: ' . ROOT_URL . 'admin/');
    die();
}

//fetch categories from db

$query = "SELECT * FROM categories ORDER BY title";
$categories = mysqli_query($connection, $query);
?>

<section class="dashboard">



<?php if(isset($_SESSION['add-category-success'])) : //Category adding was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['add-category-success'];
                unset($_SESSION['add-category-success']);
                ?>
            </p>
        </div>
        
        <?php elseif(isset($_SESSION['add-category'])) : //Category adding was unsuccessful ?>
        <div class="alert__message error container">
            <p>
                <?= $_SESSION['add-category'];
                unset($_SESSION['add-category']);
                ?>
            </p>
        </div>
        
        <?php elseif(isset($_SESSION['edit-category'])) : //Category edit was unsuccessful ?>
        <div class="alert__message error container">
            <p>
                <?= $_SESSION['edit-category'];
                unset($_SESSION['edit-category']);
                ?>
            </p>
        </div>
        
        <?php elseif(isset($_SESSION['edit-category-success'])) : //Category edit was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['edit-category-success'];
                unset($_SESSION['edit-category-success']);
                ?>
            </p>
        </div>
        
        <?php elseif(isset($_SESSION['delete-category-success'])) : //Category deleted was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['delete-category-success'];
                unset($_SESSION['delete-category-success']);
                ?>
            </p>
        </div>


        <?php endif ?>


    <div class="container dashboard__container">


        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-right"></i></button>
        <button id="hide__sidebar-btn"class="sidebar__toggle"><i class="fas fa-chevron-left"></i></button>

        <aside>
            <ul>
                <li><a href="add-post.php"><i class="fas fa-blog"></i>
                    <h5>Add Post</h5>
                </a></li>

                <li><a href="index.php"><i class="fas fa-pencil"></i>
                    <h5>Manage Posts</h5>
                </a></li>
                <?php if(isset($_SESSION['user_is_admin'])): ?>
                <li><a href="add-user.php"><i class="fas fa-user-plus"></i>
                    <h5>Add User</h5>
                </a></li>

                <li><a href="manage-users.php"><i class="fas fa-user-cog"></i>
                    <h5>Manage Users</h5>
                </a></li>

                <li><a href="add-category.php"><i class="fas fa-edit"></i>
                    <h5>Add Category</h5>
                </a></li>

                <li><a href="manage-categories.php" class="active"><i class="fas fa-list"></i>
                    <h5>Manage Category</h5>
                </a></li>
                <?php endif  ?>
            </ul>
        </aside>
        
        <main>
            <h2>Manage Categories</h2>

        <?php if(mysqli_num_rows($categories) > 0) : ?>    

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = mysqli_fetch_assoc($categories)) : ?>
                    <tr>
                        <td><?= $category['title'] ?></td>
                        <td><a href="<?= ROOT_URL ?>admin/edit-category.php?id=<?= $category['id'] ?>" class="btn sm">Edit</a></td>
                        <td><a href="<?= ROOT_URL ?>admin/delete-category.php?id=<?= $category['id'] ?>" class="btn sm danger">Delete</a></td>
                    </tr>
                    <?php endwhile ?>
                </tbody>
            </table>

            <?php else : ?>
                <div class="alert__message error"><?= "No Categories Found" ?></div>
                <?php endif ?>

        </main>
    </div>
</section>

<?php
include '../partials/footer-auth.php';
?>