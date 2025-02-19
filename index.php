<?php
require 'partials/header.php';
require 'helpers/format_time.php';

// is_featured post
$featured_query = "SELECT * FROM posts WHERE is_featured=1";
$featured_result = mysqli_query($connection, $featured_query);
$featured = mysqli_fetch_assoc($featured_result);

// retrive the posts from db 
$query = "SELECT * FROM posts ORDER BY date_time DESC LIMIT 9";
$posts = mysqli_query($connection, $query);
?>

<!--    Retrive the is_featured post --->
<?php if ($featured && mysqli_num_rows($featured_result) == 1) : ?>
    <section class="featured">
        <div class="container featured__container" style="background-image: url('<?= ROOT_URL ?>images/<?= $featured['thumbnail'] ?>');">
            <div class="featured__post">
                <div class="featured__post-content">
                    <h2 class="post__title">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $featured['id'] ?>"><?= $featured['title'] ?></a>
                    </h2>
                    <?php
                    // get categories from db 
                    $post_id = $featured['id'];
                    $category_query = "SELECT c.* FROM categories c 
                                     JOIN post_categories pc ON c.id = pc.category_id 
                                     WHERE pc.post_id = $post_id";
                    $category_result = mysqli_query($connection, $category_query);
                    
                    if ($category_result && mysqli_num_rows($category_result) > 0) {
                        while ($category = mysqli_fetch_assoc($category_result)) {
                            ?>
                            <a href="<?= ROOT_URL ?>category.php?id=<?= $category['id'] ?>" class="category__button">
                                <?= $category['title'] ?>
                            </a>
                            <?php
                        }
                    } else {
                        echo "<p>No category assigned</p>";
                    }
                    ?>
                    <p class="post__body">
                        <?= substr($featured['body'], 0, 300) ?>...
                    </p>
                    <div class="post__author">
                        <?php
                        //fetch author from users table
                        $author_id = $featured['author_id'];
                        $author_query = "SELECT * FROM users WHERE id=$author_id";
                        $author_result = mysqli_query($connection, $author_query);
                        $author = mysqli_fetch_assoc($author_result);
                        ?>
                        <div class="post__author-avatar">
                            <img src="<?= ROOT_URL ?>images/<?= $author['avatar'] ?>" alt="">
                        </div>
                        <div class="post__author-info">
                            <h5>By: <?= "{$author['firstname']} {$author['lastname']}" ?></h5>
                            <small>
                                <?= date("M d, Y - H:i", strtotime($featured['date_time'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php endif ?>
<!---     end feature  -->

<section class="posts <?= $featured ? '' : 'section__extra-margin' ?>">
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
                </div>
            </article>
        <?php endwhile ?>
    </div>
</section>

<!--  end post -->

<?php include 'partials/cta.php'; ?>

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