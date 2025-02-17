<?php
include 'partials/header.php';

// Get post data
if(isset($_GET['id'])) {
    $post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE id=$post_id";
    $result = mysqli_query($connection, $query);
    $post = mysqli_fetch_assoc($result);

    // Get post categories
    $category_query = "SELECT c.* FROM categories c 
                      JOIN post_categories pc ON c.id = pc.category_id 
                      WHERE pc.post_id = $post_id";
    $category_result = mysqli_query($connection, $category_query);
    $post_categories = [];
    while($category = mysqli_fetch_assoc($category_result)) {
        $post_categories[] = $category;
    }

    // Get all categories
    $all_categories_query = "SELECT * FROM categories ORDER BY title";
    $all_categories = mysqli_query($connection, $all_categories_query);
} else {
    header('location: ' . ROOT_URL . 'admin/');
    die();
}
?>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Edit Post</h2>
        <?php if(isset($_SESSION['edit-post'])) : ?>
            <div class="alert__message error">
                <p><?= $_SESSION['edit-post']; unset($_SESSION['edit-post']); ?></p>
            </div>
        <?php endif ?>

        <!-- Quick Category Creation -->
        <div class="quick-category">
            <input type="text" id="new-category" placeholder="New Category">
            <button class="btn sm" onclick="addNewCategory()">Add Category</button>
        </div>

        <form action="<?= ROOT_URL ?>admin/edit-post-logic.php" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <input type="hidden" name="previous_thumbnail" value="<?= $post['thumbnail'] ?>">
            <input type="text" name="title" value="<?= $post['title'] ?>" placeholder="Title">
            
            <div class="categories-wrapper">
                <h4>Select Categories</h4>
                <div class="categories-selector">
                    <?php while($category = mysqli_fetch_assoc($all_categories)): ?>
                        <div class="category-item" 
                             data-id="<?= $category['id'] ?>" 
                             onclick="toggleCategory(<?= $category['id'] ?>, '<?= $category['title'] ?>')"
                             <?php if(in_array($category['id'], array_column($post_categories, 'id'))): ?>
                                class="selected"
                             <?php endif; ?>>
                            <?= $category['title'] ?>
                        </div>
                    <?php endwhile ?>
                </div>
                <div class="selected-categories" id="selected-categories">
                    <?php if(empty($post_categories)): ?>
                        <em style="color: var(--color-gray-300)">Selected categories will appear here</em>
                    <?php endif ?>
                </div>
                <input type="hidden" name="categories[]" id="categories-input" value="<?= implode(',', array_column($post_categories, 'id')) ?>">
            </div>

            <textarea rows="10" name="body" placeholder="Body"><?= $post['body'] ?></textarea>
            <div class="form__control">
                <label for="thumbnail">Change Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail">
            </div>
            <?php if(isset($_SESSION['user_is_admin'])) : ?>
                <div class="form__control inline">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" <?= $post['is_featured'] ? 'checked' : '' ?>>
                    <label for="is_featured">Featured</label>
                </div>
            <?php endif ?>
            <button type="submit" name="submit" class="btn">Update Post</button>
        </form>
    </div>
</section>

<script>
const ROOT_URL = '<?= ROOT_URL ?>';
selectedCategories = <?= json_encode(array_map(function($cat) {
    return ['id' => $cat['id'], 'title' => $cat['title']];
}, $post_categories)) ?>;
updateSelectedCategories();
</script>
<script src="<?= ROOT_URL ?>admin/js/categories.js"></script>
