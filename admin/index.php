<?php
require '../config/database.php';  // Load database connection first
include 'partials/header.php';    // Then include admin header

// current user's posts in db
$current_user_id = $_SESSION['user-id'];

// Updated query to handle the new post_categories table structure
$query = "SELECT p.id, p.title FROM posts p 
          WHERE p.author_id = $current_user_id 
          ORDER BY p.id DESC";

$posts = mysqli_query($connection, $query);

// Check if query was successful
if (!$posts) {
    die("Query Failed: " . mysqli_error($connection));
}

?>


<?php if(isset($_SESSION['add-post-success'])) : //Post adding was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['add-post-success'];
                unset($_SESSION['add-post-success']);
                ?>
            </p>
        </div>
        <?php elseif(isset($_SESSION['edit-post-success'])) : //Post edit was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['edit-post-success'];
                unset($_SESSION['edit-post-success']);
                ?>
            </p>
        </div>
        <?php elseif(isset($_SESSION['edit-post'])) : //Post edit was unsuccessful ?>
        <div class="alert__message error container">
            <p>
                <?= $_SESSION['edit-post'];
                unset($_SESSION['edit-post']);
                ?>
            </p>
        </div> 

        <?php elseif(isset($_SESSION['delete-post-success'])) : //Post delete was successful ?>
        <div class="alert__message success container">
            <p>
                <?= $_SESSION['delete-post-success'];
                unset($_SESSION['delete-post-success']);
                ?>
            </p>
        </div>
<?php endif ?> 
<section class="dashboard">
    <div class="container dashboard__container">


        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-right"></i></button>
        <button id="hide__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-left"></i></button>

        <aside>
            <ul>
                <li><a href="add-post.php"><i class="fas fa-pen"></i>
                    <h5>Add Post</h5>
                </a></li>
                <li><a href="<?= ROOT_URL ?>admin/index.php" class="active"><i class="fas fa-pencil"></i>
                    <h5>Manage Posts</h5>
                </a></li>
                <li><a href="edit-profile.php"><i class="fas fa-user-edit"></i>
                    <h5>Edit Profile</h5>
                </a></li>
                <?php if($_SESSION['user_is_admin']): ?>
                    <li><a href="add-user.php"><i class="fas fa-user-plus"></i>
                        <h5>Add User</h5>
                    </a></li>
                    <li><a href="manage-users.php"><i class="fas fa-user-cog"></i>
                        <h5>Manage Users</h5>
                    </a></li>
                    <li><a href="manage-categories.php"><i class="fas fa-list"></i>
                        <h5>Manage Categories</h5>
                    </a></li>
                <?php endif ?>
                <li><a href="add-category.php"><i class="fas fa-edit"></i>
                    <h5>Add Category</h5>
                </a></li>
            </ul>
        </aside>

        <main>
            <h2>Manage Posts</h2>

            <?php if(mysqli_num_rows($posts) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Categories</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($post = mysqli_fetch_assoc($posts)) : ?>
                        <?php
                            // Get categories for this post
                            $post_id = $post['id'];
                            $category_query = "SELECT c.title 
                                             FROM categories c 
                                             JOIN post_categories pc ON c.id = pc.category_id 
                                             WHERE pc.post_id = $post_id";
                            $category_result = mysqli_query($connection, $category_query);
                            
                            // Create array of category titles
                            $categories = [];
                            while($category = mysqli_fetch_assoc($category_result)) {
                                $categories[] = $category['title'];
                            }
                        ?>
                        <tr>
                            <td><?= $post['title'] ?></td>
                            <td><?= implode(', ', $categories) ?></td>
                            <td><a href="<?= ROOT_URL ?>admin/edit-post.php?id=<?= $post['id'] ?>" class="btn sm">Edit</a></td>
                            <td><a href="#" class="btn sm danger" onclick="showDeleteModal(<?= $post['id'] ?>, '<?= addslashes($post['title']) ?>')">Delete</a></td>
                        </tr>
                    <?php endwhile ?>
                </tbody>
            </table>

            <!-- Add Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <h3>Confirm Deletion</h3>
                    <p>Are you sure you want to delete post: <span id="postTitle"></span>?</p>
                    <div class="modal-buttons">
                        <button onclick="hideDeleteModal()" class="btn">Cancel</button>
                        <a href="#" id="confirmDelete" class="btn danger">Delete</a>
                    </div>
                </div>
            </div>

            <?php else : ?>
                <div class="alert__message error"><?= "No posts found" ?></div>
            <?php endif ?>

        </main>
    </div>
</section>

<!-- Add before closing </body> tag -->
<script>
    function showDeleteModal(postId, postTitle) {
        const modal = document.getElementById('deleteModal');
        const postTitleSpan = document.getElementById('postTitle');
        const confirmButton = document.getElementById('confirmDelete');
        
        postTitleSpan.textContent = postTitle;
        confirmButton.href = '<?= ROOT_URL ?>admin/delete-post.php?id=' + postId;
        modal.style.display = 'flex';
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            hideDeleteModal();
        }
    }
</script>

<?php
include '../partials/footer-auth.php';
?>