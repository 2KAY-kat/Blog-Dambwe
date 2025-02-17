<?php
include 'partials/header.php';
require 'helpers/format_time.php';

//retrive post using its id

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($connection, $query);
    $post = mysqli_fetch_assoc($result);
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
  }
?>


<section class="singlepost">
    <div class="container singlepost__container">
        <h2><?= $post['title'] ?></h2>
        <div class="post__author">

            <?php

            // fetch author name, avatar

            $author_id = $post['author_id'];
            $author_query = "SELECT * FROM users WHERE id=$author_id";
            $author_result = mysqli_query($connection, $author_query);
            $author = mysqli_fetch_assoc($author_result);

            ?>

            <div class="post__author-avatar">
                <img src="images/<?= $author['avatar'] ?>">
            </div>
            <div class="post__author-info">
                <h5>By: <?= "{$author['firstname']} {$author['lastname']}" ?></h5>
                <small title="<?= date("M d, Y - H:i", strtotime($post['date_time'])) ?>">
                    <?= timeAgo($post['date_time']) ?>
                </small>
            </div>
        </div>

    <div class="singlepost__thumbnail">
        <img src="images/<?= $post['thumbnail'] ?>">
    </div>

    <p>
    <?= $post['body'] ?>
    </p>
    </div>
</section>

<?php
// After displaying the current post, add this section for recommendations
$current_post_id = $post['id'];

// Get categories of current post
$category_query = "SELECT category_id FROM post_categories WHERE post_id = $current_post_id";
$category_result = mysqli_query($connection, $category_query);
$category_ids = [];
while($category = mysqli_fetch_assoc($category_result)) {
    $category_ids[] = $category['category_id'];
}

if(!empty($category_ids)) {
    // Get related posts that share categories with current post
    $categories_list = implode(',', $category_ids);
    $related_posts_query = "SELECT DISTINCT p.*, u.firstname, u.lastname, u.avatar 
                           FROM posts p 
                           JOIN post_categories pc ON p.id = pc.post_id 
                           JOIN users u ON p.author_id = u.id 
                           WHERE pc.category_id IN ($categories_list) 
                           AND p.id != $current_post_id 
                           LIMIT 3";
    $related_posts_result = mysqli_query($connection, $related_posts_query);
?>

    <?php if(!empty($category_ids)) : ?>
        <section class="posts recommended-posts">
        <h2 class="contained" style="text-align: center; margin-bottom: 2rem;">Similar Posts</h2>
            <div class="container posts__container">
                
                <?php while($related_post = mysqli_fetch_assoc($related_posts_result)): ?>
                    <article class="post">
                        <div class="post__thumbnail">
                            <img src="<?= ROOT_URL ?>images/<?= $related_post['thumbnail'] ?>">
                        </div>
                        <div class="post__info">
                            <h3 class="post__title">
                                <a href="<?= ROOT_URL ?>post.php?id=<?= $related_post['id'] ?>">
                                    <?= $related_post['title'] ?>
                                </a>
                            </h3>
                            <p class="post__body">
                                <?= substr($related_post['body'], 0, 150) ?>...
                            </p>
                            <div class="post__author">
                                <div class="post__author-avatar">
                                    <img src="<?= ROOT_URL ?>images/<?= $related_post['avatar'] ?>">
                                </div>
                                <div class="post__author-info">
                                    <div class="post__author-info">
                                        <h5>By: <?= "{$related_post['firstname']} {$related_post['lastname']}" ?></h5>
                                        <small><?= timeAgo($related_post['date_time']) ?></small>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile ?>
                </div>
            </div>
        </section>
    <?php endif ?>
<?php } ?>

<!------------ end single post  ----------------------->



<?php
include 'partials/footer.php';


?>