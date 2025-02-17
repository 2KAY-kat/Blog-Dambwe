<?php

/******************************************************************************
*******************************************************************************
********** this the search scrips done after some modifications and **********/
/*********  a couple of trial and errors if anything goes sideways  **********/
/********* scroll down the page you disabled the og one you made    **********/ 
/********* mofo and uncomment. everything should still work unless  **********/
/********* you do something really stupid. but its working i trust  **********/
/*********        you if needed we go back to it in a flash         **********/
?>

<?php 
require 'partials/header.php';
require __DIR__ . '/helpers/format_time.php';  // Update this line to use absolute path

if(isset($_GET['search']) && isset($_GET['submit'])) {
    $search = filter_var($_GET['search'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $search = mysqli_real_escape_string($connection, $search);

    // Updated query to include categories through post_categories table
    $query = "SELECT DISTINCT p.*, GROUP_CONCAT(c.title) as categories, 
                     u.firstname, u.lastname, u.avatar
              FROM posts p 
              LEFT JOIN users u ON p.author_id = u.id
              LEFT JOIN post_categories pc ON p.id = pc.post_id 
              LEFT JOIN categories c ON pc.category_id = c.id 
              WHERE p.title LIKE '%$search%' 
              OR p.body LIKE '%$search%'
              OR c.title LIKE '%$search%'
              OR u.firstname LIKE '%$search%'
              OR u.lastname LIKE '%$search%'
              GROUP BY p.id
              ORDER BY p.date_time DESC";

    $posts = mysqli_query($connection, $query);

    if(!$posts) {
        die("Search Query Failed: " . mysqli_error($connection));
    }
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
}
?>

<section class="posts section__extra-margin">
    <div class="container posts__container">
        <?php if(mysqli_num_rows($posts) > 0) : ?>
            <?php while ($post = mysqli_fetch_assoc($posts)) : ?>
                <article class="post">
                    <div class="post__thumbnail">
                        <img src="<?= ROOT_URL ?>images/<?= $post['thumbnail'] ?>">
                    </div>
                    <div class="post__info">
                        <?php if($post['categories']): ?>
                            <div class="post__categories">
                                <?php foreach(explode(',', $post['categories']) as $category): ?>
                                    <span class="category__button"><?= $category ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="post__title">
                            <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                        </h3>
                        <p class="post__body">
                            <?= substr($post['body'], 0, 150) ?>...
                        </p>
                        <div class="post__author">
                            <div class="post__author-avatar">
                                <img src="<?= ROOT_URL ?>images/<?= $post['avatar'] ?>">
                            </div>
                            <div class="post__author-info">
                                <h5>By: <?= "{$post['firstname']} {$post['lastname']}" ?></h5>
                                <small><?= format_time($post['date_time']) ?></small>  <!-- Update this line -->
                            </div>
                        </div>
                    </div>
                </article>
            <?php endwhile ?>
        <?php else : ?>
            <div class="alert__message error">
                <p>No posts found for "<?= htmlspecialchars($search) ?>"</p>
            </div>
        <?php endif ?>
    </div>
</section>

<!-- Category Buttons -->
<section class="category__buttons">
    <div class="container category__buttons-container">
        <?php
        $all_categories_query = "SELECT * FROM categories";
        $all_categories = mysqli_query($connection, $all_categories_query);
        ?>
        <?php while ($category = mysqli_fetch_assoc($all_categories)) : ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= $category['title'] ?></a>
        <?php endwhile ?>
    </div>
</section>

<?php include 'partials/footer.php'; ?>





<?php
/******************************************************************************
*******************************************************************************
******** this the og search page if needed we go back to it in a flash ********
<?php 
require 'partials/header.php';

// if search is done

if(isset($_GET['search']) && isset($_GET['submit'])) {
    $search = filter_var($_GET['search'], FILTER_SANITIZE_SPECIAL_CHARS);
    $query = "SELECT * FROM posts WHERE title LIKE '%$search%' OR body LIKE '%$search%' ORDER BY date_time DESC";
    $posts = mysqli_query($connection, $query);
} else {
    header('location: ' . ROOT_URL . 'blog.php');
}

?>



<?php if(mysqli_num_rows($posts) > 0) : ?>

<section class="posts section__extra-margin">
    <div class="container posts__container">

        <?php while ($post = mysqli_fetch_assoc($posts)) : ?>
            <article class="post">
                <div class="post__thumbnail">
                    <img src="images/<?= $post['thumbnail'] ?>">
                </div>
                <div class="post__info">
                    <?php
                    // get categories from db 

                    $category_id = $post['category_id'];
                    $category_query = "SELECT * FROM categories WHERE id=$category_id";
                    $category_result = mysqli_query($connection, $category_query);
                    $category = mysqli_fetch_assoc($category_result);
                    ?>
                    <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $post['category_id'] ?>" class="category__button"><?= $category['title'] ?></a>
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
                            <img src="images/<?= $author['avatar'] ?>">
                        </div>
                        <div class="post__author-info">
                            <h5>
                                <h5>By: <?= "{$author['firstname']} {$author['lastname']}" ?></h5>
                            </h5>
                            <small><?= date("M d, Y - H:i", strtotime($post['date_time'])) ?></small>
                        </div>
                    </div>
                </div>
            </article>
        <?php endwhile ?>
    </div>
</section>

<?php else : ?>
    <div class="alert__message error lg section__extra-margin">
        <p>
            No Posts found for this search
        </p>
    </div>
<?php endif ?>

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

*/
?>
