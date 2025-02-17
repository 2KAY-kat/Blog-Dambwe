<?php
include 'partials/header.php';
$query = "SELECT * FROM categories ORDER BY title";
$categories = mysqli_query($connection, $query);
?>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Add Post</h2>
        <?php if(isset($_SESSION['add-post'])) : ?>
            <div class="alert__message error">
                <p><?= $_SESSION['add-post']; unset($_SESSION['add-post']); ?></p>
            </div>
        <?php endif ?>

        <!-- Quick Category Creation -->
        <div class="quick-category">
            <input type="text" id="new-category" placeholder="New Category">
            <button class="btn sm" onclick="addNewCategory()">Add Category</button>
        </div>

        <form action="<?= ROOT_URL ?>admin/add-post-logic.php" enctype="multipart/form-data" method="POST">
            <input type="text" name="title" placeholder="Title">
            
            <div class="categories-wrapper">
                <div class="selected-categories" id="selected-categories">
                    <!-- Selected categories will appear here as tags -->
                </div>
                <div class="categories-selector">
                    <?php while($category = mysqli_fetch_assoc($categories)): ?>
                        <div class="category-item" data-id="<?= $category['id'] ?>" onclick="toggleCategory(<?= $category['id'] ?>, '<?= $category['title'] ?>')">
                            <?= $category['title'] ?>
                        </div>
                    <?php endwhile ?>
                </div>
                <!-- Hidden input to store selected categories -->
                <input type="hidden" name="categories[]" id="categories-input" value="">
            </div>

            <textarea rows="10" name="body" placeholder="Body"><?= $body ?? '' ?></textarea>
            <div class="form__control">
                <label for="thumbnail">Add Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail">
            </div>
            <?php if(isset($_SESSION['is_admin'])) : ?>
                <div class="form__control inline">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured">
                    <label for="is_featured">Featured</label>
                </div>
            <?php endif ?>
            <button class="btn" name="submit" type="submit">Add Post</button>
        </form>
    </div>
</section>

<!-- Add this before the closing body tag -->
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