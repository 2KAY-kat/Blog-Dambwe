<section class="cta">
    <div class="container cta__container">
        <h2>Ready to Share Your Story?</h2>
        <p>Join our community of writers and share your knowledge with the world.</p>
        <?php if(isset($_SESSION['user-id'])): ?>
            <a href="<?= ROOT_URL ?>admin/add-post.php" class="btn cta__btn">
                <i class="fas fa-pencil-alt"></i> Start Writing
            </a>
        <?php else: ?>
            <a href="<?= ROOT_URL ?>signin.php" class="btn cta__btn">
                <i class="fas fa-sign-in-alt"></i> Sign In to Write
            </a>
        <?php endif; ?>
    </div>
</section>
