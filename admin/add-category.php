<?php
include 'partials/header.php';
// Remove any admin-only checks
?>

<section class="dashboard">
    <main>
    <section class="container form__section-container">
            <h2>Add Category</h2>
            <?php if(isset($_SESSION['add-category'])) : ?>
                <div class="alert__message error">
                    <p>
                        <?= $_SESSION['add-category'];
                        unset($_SESSION['add-category']);
                        ?>
                    </p>
                </div>
            <?php endif ?>
            <form action="<?= ROOT_URL ?>admin/add-category-logic.php" method="POST">
                <input type="text" name="title" placeholder="Category Title">
                <textarea rows="4" name="description" placeholder="Description"></textarea>
                <button type="submit" name="submit" class="btn">Add Category</button>
            </form>
        </section>
    </main>
</section>

<?php
include '../partials/footer-auth.php';
?>