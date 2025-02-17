<?php
include 'partials/header.php';
require 'helpers/format_time.php';

// Fetch author from database
if(isset($_GET['id'])) {
    $author_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM users WHERE id=$author_id";
    $result = mysqli_query($connection, $query);
    $author = mysqli_fetch_assoc($result);

    // Fetch author's posts
    $posts_query = "SELECT * FROM posts WHERE author_id=$author_id ORDER BY date_time DESC";
    $posts_result = mysqli_query($connection, $posts_query);
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
}
?>

<section class="author__posts">
    <div class="author__info">
        <img src="<?= ROOT_URL . 'images/' . $author['avatar'] ?>">
        <span class="author-title">Author</span>
        <h1><?= "{$author['firstname']} {$author['lastname']}" ?></h1>
        <?php if($author['bio']): ?>
            <p><?= $author['bio'] ?></p>
        <?php endif ?>
    </div>

    <?php if(mysqli_num_rows($posts_result) > 0) : ?>
    <div class="author__posts-container">
        <h2>Latest Posts by <?= "{$author['firstname']}" ?></h2>
        <div class="posts__container">
            <?php while($post = mysqli_fetch_assoc($posts_result)) : ?>
                <article class="post">
                    <div class="post__thumbnail">
                        <img src="<?= ROOT_URL . 'images/' . $post['thumbnail'] ?>">
                    </div>
                    <div class="post__info">
                        <h3><a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a></h3>
                        <p class="post__body"><?= substr($post['body'], 0, 150) ?>...</p>
                    </div>
                </article>
            <?php endwhile ?>
        </div>
    </div>
    <?php else : ?>
        <div class="alert__message error">
            <p>No posts found for this author</p>
        </div>
    <?php endif ?>
</section>

<?php include 'partials/footer.php' ?>
