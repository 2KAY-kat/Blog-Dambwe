<?php
include 'partials/header.php';

// Get category from URL
if (isset($_GET['id'])) {
    $category_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // Get category data
    $category_query = "SELECT * FROM categories WHERE id=$category_id";
    $category_result = mysqli_query($connection, $category_query);
    
    if (!$category_result || mysqli_num_rows($category_result) == 0) {
        header('location: ' . ROOT_URL . 'blog.php');
        die();
    }
    
    $category = mysqli_fetch_assoc($category_result);

    // Get posts for this category using the post_categories junction table
    $posts_query = "SELECT p.*, u.firstname, u.lastname, u.avatar 
                   FROM posts p 
                   JOIN users u ON p.author_id = u.id 
                   JOIN post_categories pc ON p.id = pc.post_id 
                   WHERE pc.category_id = $category_id 
                   ORDER BY p.date_time DESC";
    
    $posts_result = mysqli_query($connection, $posts_query);

    if (!$posts_result) {
        // Handle query error
        die("Query Failed: " . mysqli_error($connection));
    }
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
}
?>

<header class="category__title">
    <h2>
        <?php
        echo $category['title'];
        ?>
    </h2>
</header>

<!---   end -->


<?php if(mysqli_num_rows($posts_result) > 0) : ?>
<section class="posts">
    <div class="container posts__container">

        <?php while ($post = mysqli_fetch_assoc($posts_result)) : ?>
            <article class="post">
                <div class="post__thumbnail">
                    <img src="images/<?= $post['thumbnail'] ?>">
                </div>
                <div class="post__info">
                    <h3 class="post__title">
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                    </h3>
                    <p class="post__body">
                        <?= substr($post['body'], 0, 170) ?>...
                    </p>
                    <div class="post__author">
                        <div class="post__author-avatar">
                            <img src="images/<?= $post['avatar'] ?>">
                        </div>
                        <div class="post__author-info">
                            <h5>By: <?= "{$post['firstname']} {$post['lastname']}" ?></h5>
                            <small><?= date("M d, Y - H:i", strtotime($post['date_time'])) ?></small>
                        </div>
                    </div>
                </div>
            </article>
        <?php endwhile ?>
    </div>
</section>

<?php else : ?>
    <div class="alert__message error lg">
        <p>
            No Posts found for this category
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