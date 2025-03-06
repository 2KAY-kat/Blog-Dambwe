<?php
require 'config/database.php';

// Get user ID from session or set to 0 if not logged in
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0;

// Fetch recent posts with reaction and comment counts
$query = "SELECT p.*, u.avatar, u.firstname, u.lastname,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'like') as likes_count,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
            (SELECT type FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
          FROM posts p 
          JOIN users u ON u.id = p.author_id
          WHERE p.is_featured = 0 
          ORDER BY p.date_time DESC 
          LIMIT 5";

// Prepare statement and check for errors
$stmt = mysqli_prepare($connection, $query);
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($connection));
}

// Bind parameter and check for errors
if (!mysqli_stmt_bind_param($stmt, "i", $user_id)) {
    die("Binding parameters failed: " . mysqli_stmt_error($stmt));
}

// Execute statement and check for errors
if (!mysqli_stmt_execute($stmt)) {
    die("Execute failed: " . mysqli_stmt_error($stmt));
}

$posts = mysqli_stmt_get_result($stmt);

// Check for query errors
if (!$posts) {
    die("Getting results failed: " . mysqli_error($connection));
}

include 'partials/header.php';
?>

<section class="recent-posts">
    <div class="container recent-posts__container">
        <h1>Recent Posts</h1>
        
        <?php if(mysqli_num_rows($posts) > 0) : ?>
        <div class="posts__container">
            <?php while($post = mysqli_fetch_assoc($posts)) : ?>
            <article class="post">
                <div class="post__thumbnail">
                    <img src="./images/<?= $post['thumbnail'] ?>" alt="<?= $post['title'] ?>">
                </div>
                <div class="post__info">
                    <?php
                    // Get categories for this post
                    $post_id = $post['id'];
                    $category_query = "SELECT c.* FROM categories c 
                                     JOIN post_categories pc ON c.id = pc.category_id 
                                     WHERE pc.post_id = ?";
                    $cat_stmt = mysqli_prepare($connection, $category_query);
                    mysqli_stmt_bind_param($cat_stmt, "i", $post_id);
                    mysqli_stmt_execute($cat_stmt);
                    $category_result = mysqli_stmt_get_result($cat_stmt);
                    ?>
                    <div class="post__categories">
                        <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>"
                                class="category__button"><?= $category['title'] ?></a>
                        <?php endwhile ?>
                    </div>
                    <h3 class="post__title">
                        <a href="post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                    </h3>
                    <p class="post__body">
                        <?= substr($post['body'], 0, 150) ?>...
                    </p>
                    <div class="post__author">
                        <div class="post__author-avatar">
                            <img src="./images/<?= $post['avatar'] ?>" alt="<?= $post['firstname'] . ' ' . $post['lastname'] ?>">
                        </div>
                        <div class="post__author-info">
                            <h5>By: <?= "{$post['firstname']} {$post['lastname']}" ?></h5>
                            <small><?= date("M d, Y - H:i", strtotime($post['date_time'])) ?></small>
                        </div>
                    </div>
                    <div class="post__interactions">
                        <span class="interaction-item <?= ($post['user_reaction'] === 'like') ? 'active' : '' ?>" 
                              data-post-id="<?= $post['id'] ?>" 
                              data-action="like">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="interaction-count"><?= $post['likes_count'] ?? 0 ?></span>
                        </span>
                        <span class="interaction-item <?= ($post['user_reaction'] === 'dislike') ? 'active' : '' ?>" 
                              data-post-id="<?= $post['id'] ?>" 
                              data-action="dislike">
                            <i class="fas fa-thumbs-down"></i>
                            <span class="interaction-count"><?= $post['dislikes_count'] ?? 0 ?></span>
                        </span>
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>#comments-section">
                            <i class="fas fa-comment"></i>
                            <span><?= $post['comment_count'] ?></span>
                        </a>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
        <?php else : ?>
        <div class="alert__message error">
            <p>No posts found</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.recent-posts {
    padding: 5rem 0;
}

.recent-posts__container {
    width: var(--container-width-lg);
    margin: 0 auto;
}

.recent-posts__container h1 {
    text-align: center;
    margin-bottom: 3rem;
    color: var(--color-white);
}

.posts__container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4rem;
}

@media screen and (max-width: 1024px) {
    .posts__container {
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
    }
}

@media screen and (max-width: 600px) {
    .posts__container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.userId = <?= isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 'null' ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= ROOT_URL ?>js/global.js"></script>
<script src="<?= ROOT_URL ?>js/interactions-likes.js"></script>

<?php include 'partials/footer.php'; ?>