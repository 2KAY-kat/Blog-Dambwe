<?php
include 'partials/header.php';

// retrive the posts from db 
$query = "SELECT * FROM posts ORDER BY date_time DESC LIMIT 9";
$posts = mysqli_query($connection, $query);
?>



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

                    <div class="post__author-info share__post">
                            <p class="whatsapp-share">Share This post <i class="far fa-share-from-square"></i></p>
                            <p class="like-container"><i class="fa fa-thumbs-up" aria-hidden="true"></i>Like</p>
                        </div>
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