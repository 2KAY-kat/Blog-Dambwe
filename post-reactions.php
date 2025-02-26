<?php
require 'partials/header.php';

if (!isset($_GET['id'])) {
    header('location: ' . ROOT_URL . 'blog.php');
    exit;
}

$post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Get post details
$post_query = "SELECT title FROM posts WHERE id = ?";
$stmt = mysqli_prepare($connection, $post_query);
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$post = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$post) {
    header('location: ' . ROOT_URL . 'blog.php');
    exit();
}
?>

<div class="container reactions-page">
    <!-- Debug info
    <div id="debug" style="padding: 1rem; background: #333; color: #fff; margin: 1rem;">
        Post ID: ?= $post_id ?> <br>
        ROOT_URL:?=/* ROOT_URL ?>
    </div>
-->
    
    <div class="reactions-tabs">
        <button class="tab-btn active" data-tab="likes">Likes</button>
        <button class="tab-btn" data-tab="dislikes">Dislikes</button>
    </div>

    <div class="reactions-container">
        <div id="likes" class="tab-content active">
            <div class="loader"></div>
            <div class="reactions-list"></div>
        </div>
        <div id="dislikes" class="tab-content">
            <div class="loader"></div>
            <div class="reactions-list"></div>
        </div>
    </div>
</div>

<script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.postId = <?= $post_id ?>;
</script>
<script src="<?= ROOT_URL ?>js/reactions-page.js"></script>

<?php require 'partials/footer-auth.php'; ?>
