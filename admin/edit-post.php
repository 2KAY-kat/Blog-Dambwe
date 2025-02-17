<?php
include 'partials/header.php';

// Get post data
$post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$query = "SELECT * FROM posts WHERE id=$post_id";
$result = mysqli_query($connection, $query);
$post = mysqli_fetch_assoc($result);

// Get post categories
$post_categories_query = "SELECT category_id FROM post_categories WHERE post_id=$post_id";
$post_categories_result = mysqli_query($connection, $post_categories_query);
$post_category_ids = [];
while ($cat = mysqli_fetch_assoc($post_categories_result)) {
    $post_category_ids[] = $cat['category_id'];
}

// Get all categories
$categories_query = "SELECT * FROM categories ORDER BY title";
$categories = mysqli_query($connection, $categories_query);
?>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Edit Post</h2>
        <?php if (isset($_SESSION['edit-post'])) : ?>
            <div class="alert__message error">
                <p><?= $_SESSION['edit-post'];
                    unset($_SESSION['edit-post']); ?></p>
            </div>
        <?php endif ?>

        <!-- Quick Category Creation -->
        <div class="quick-category">
            <input type="text" id="new-category" placeholder="New Category">
            <button class="btn" onclick="addNewCategory()">Add Category</button>
        </div><br />

        <form action="<?= ROOT_URL ?>admin/edit-post-logic.php" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <input type="hidden" name="previous_thumbnail" value="<?= $post['thumbnail'] ?>">
            <input type="text" name="title" value="<?= $post['title'] ?>" placeholder="Title">

            <div class="categories-wrapper">
                <h4>Select Categories</h4>
                <div class="categories-selector">
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                        <div class="category-item"
                            data-id="<?= $category['id'] ?>"
                            onclick="toggleCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['title'], ENT_QUOTES) ?>')"
                            <?php if (isset($post_categories) && in_array($category['id'], array_column($post_categories, 'id'))): ?>
                            class="selected"
                            <?php endif; ?>>
                            <?= htmlspecialchars($category['title']) ?>
                        </div>
                    <?php endwhile ?>
                </div>
                <div class="selected-categories" id="selected-categories">
                    <em style="color: var(--color-gray-300)">Select at least one category</em>
                </div>
                <input type="hidden" name="categories[]" id="categories-input" value="" required>
            </div>

            <textarea rows="10" name="body" placeholder="Body"><?= $post['body'] ?></textarea>
            <div class="form__control">
                <label for="thumbnail">Change Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail">
            </div>
            <?php if (isset($_SESSION['user_is_admin'])) : ?>
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
let selectedCategories = [];

function toggleCategory(id, title) {
    const index = selectedCategories.findIndex(cat => cat.id === id);
    const element = document.querySelector(`.category-item[data-id="${id}"]`);
    
    if (index === -1) {
        selectedCategories.push({ id, title });
        element.classList.add('selected');
    } else {
        selectedCategories.splice(index, 1);
        element.classList.remove('selected');
    }
    
    updateSelectedCategories();
}

function updateSelectedCategories() {
    const container = document.getElementById('selected-categories');
    const input = document.getElementById('categories-input');
    
    container.innerHTML = selectedCategories.map(cat => `
        <span class="category-tag">
            ${cat.title}
            <span onclick="event.stopPropagation(); toggleCategory(${cat.id}, '${cat.title}')">&times;</span>
        </span>
    `).join('');
    
    // Update hidden input with category IDs
    const categoryIds = selectedCategories.map(cat => cat.id);
    input.value = categoryIds.join(',');
}

async function addNewCategory() {
    const input = document.getElementById('new-category');
    const title = input.value.trim();
    
    if (!title) return;
    
    try {
        const response = await fetch('<?= ROOT_URL ?>admin/add-category-ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ title })
        });
        
        const data = await response.json();
        if (data.success) {
            const categoriesContainer = document.querySelector('.categories-selector');
            const newCategory = document.createElement('div');
            newCategory.className = 'category-item';
            newCategory.dataset.id = data.id;
            newCategory.textContent = title;
            newCategory.onclick = () => toggleCategory(data.id, title);
            categoriesContainer.appendChild(newCategory);
            input.value = '';
            
            // Automatically select the new category
            toggleCategory(data.id, title);
        }
    } catch (error) {
        console.error('Error adding category:', error);
    }
}
</script>