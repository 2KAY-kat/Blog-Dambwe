<?php
include 'partials/header.php';
require 'helpers/format_time.php';

// retrive the posts from db all of them

$query = "SELECT *,
            (SELECT like_value FROM likes_dislikes 
             WHERE post_id = posts.id AND user_id = " . (isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0) . ") AS user_like_value,
            (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count
          FROM posts ORDER BY date_time DESC";
$posts = mysqli_query($connection, $query);

?>

<section class="search__bar">
    <form class="container search__bar-container" action="<?= ROOT_URL ?>search.php" method="get">
        <div>
            <i class="fas fa-search"></i>
            <input type="search" name="search" placeholder="Search">
        </div>
        <button type="submit" name="submit" class="btn">Go</button>
    </form>
</section>

<!---- end search -->


<section class="posts <?= $posts ? '' : 'section__extra-margin' ?>">
    <div class="container posts__container">

        <?php while ($post = mysqli_fetch_assoc($posts)) : ?>
            <article class="post">
                <div class="post__thumbnail">
                    <img src="images/<?= $post['thumbnail'] ?>">
                </div>
                <div class="post__info">
                    <?php
                    // get categories from db 
                    $post_id = $post['id'];
                    $category_query = "SELECT c.* FROM categories c 
                                      JOIN post_categories pc ON c.id = pc.category_id 
                                      WHERE pc.post_id = $post_id";
                    $category_result = mysqli_query($connection, $category_query);
                    ?>
                    <div class="post__categories">
                        <?php while($category = mysqli_fetch_assoc($category_result)): ?>
                            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" 
                               class="category__button"><?= $category['title'] ?></a>
                        <?php endwhile ?>
                    </div>
                    <h3 class="post__title">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                    </h3>
                    <p class="post__body">
                        <?= substr($post['body'], 0, 170) ?>...
                    </p>
                    <div class="post__author">
                        <?php
                        // fetch author name, avatar
                        $author_id = $post['author_id'];
                        $author_query = "SELECT * FROM users WHERE id=$author_id";
                        $author_result = mysqli_query($connection, $author_query);
                        $author = mysqli_fetch_assoc($author_result);
                        ?>
                        <div class="post__author-avatar">
                            <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $author['id'] ?>" title="View Posts">
                                <img src="images/<?= $author['avatar'] ?>">
                            </a>
                        </div>
                        <div class="post__author-info">
                            <h5>
                                <a href="<?= ROOT_URL ?>author-posts.php?id=<?= $author['id'] ?>">
                                    By: <?= "{$author['firstname']} {$author['lastname']}" ?>
                                </a>
                                <?php if(isset($_SESSION['user-id']) && $_SESSION['user-id'] == $author['id']): ?>
                                    <small><a href="<?= ROOT_URL ?>admin/profile.php">(View Profile)</a></small>
                                <?php endif; ?>
                            </h5>
                            <small title="<?= date("M d, Y - H:i", strtotime($post['date_time'])) ?>">
                                <?= timeAgo($post['date_time']) ?>
                            </small>
                        </div>
                    </div>
                    <!-- Like/Dislike Buttons -->
                    <div class="post__interactions">
                        <span class="like-btn" data-post-id="<?= $post['id'] ?>" data-action="like">
                            <i class="fa fa-thumbs-up"></i> <span id="like-count-<?= $post['id'] ?>">0</span>
                        </span>
                        <span class="dislike-btn" data-post-id="<?= $post['id'] ?>" data-action="dislike">
                            <i class="fa fa-thumbs-down"></i> <span id="dislike-count-<?= $post['id'] ?>">0</span>
                        </span>
                    </div>
                </div>
            </article>
        <?php endwhile ?>
    </div>
</section>

<!--  end post -->


<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories = mysqli_query($connection, $all_categories_query)
        ?>

        <?php while ($category = mysqli_fetch_assoc($all_categories)) : ?>
        <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= $category['title'] ?></a>
        <?php endwhile ?>
    </div>
</section>

<!------   end buttons category -->


<?php
include 'partials/footer.php';


?>