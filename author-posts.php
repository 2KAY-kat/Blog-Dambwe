<?php
include 'partials/header.php';

if(isset($_GET['id'])) {
    $author_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM users WHERE id=$author_id";
    $result = mysqli_query($connection, $query);
    $author = mysqli_fetch_assoc($result);

    // Get all counts
    $counts_query = "SELECT 
        (SELECT COUNT(*) FROM followers WHERE following_id = ?) as followers,
        (SELECT COUNT(*) FROM followers WHERE follower_id = ?) as following,
        (SELECT COUNT(*) FROM posts WHERE author_id = ?) as posts";
    $counts_stmt = mysqli_prepare($connection, $counts_query);
    mysqli_stmt_bind_param($counts_stmt, "iii", $author_id, $author_id, $author_id);
    mysqli_stmt_execute($counts_stmt);
    $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($counts_stmt));
    
    // Check if current user is following
    $is_following = false;
    if(isset($_SESSION['user-id'])) {
        $current_user_id = $_SESSION['user-id'];
        $following_check = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
        $check_stmt = mysqli_prepare($connection, $following_check);
        mysqli_stmt_bind_param($check_stmt, "ii", $current_user_id, $author_id);
        mysqli_stmt_execute($check_stmt);
        $is_following = mysqli_num_rows(mysqli_stmt_get_result($check_stmt)) > 0;
    }

    // Get author's posts
    $posts_query = "SELECT p.*, u.firstname, u.lastname, u.avatar 
                   FROM posts p 
                   JOIN users u ON p.author_id = u.id 
                   WHERE p.author_id = ? 
                   ORDER BY p.date_time DESC";
    $posts_stmt = mysqli_prepare($connection, $posts_query);
    mysqli_stmt_bind_param($posts_stmt, "i", $author_id);
    mysqli_stmt_execute($posts_stmt);
    $posts = mysqli_stmt_get_result($posts_stmt);
?>

<section class="author-profile">
    <div class="container">
        <div class="author__header">
            <div class="author__profile">
                <div class="author__avatar">
                    <img src="<?= ROOT_URL . 'images/' . $author['avatar'] ?>" alt="<?= "{$author['firstname']} {$author['lastname']}" ?>">
                </div>
                <div class="author__info">
                    <h1><?= "{$author['firstname']} {$author['lastname']}" ?></h1>
                    <p class="author__bio"><?= $author['bio'] ?? 'No bio available' ?></p>
                    <div class="author__stats">
                        <span><i class="fas fa-pencil-alt"></i> <?= $counts['posts'] ?> Posts</span> |
                        <span><i class="fas fa-users"></i> <?= $counts['followers'] ?> Followers</span> |
                        <span><i class="fas fa-user-friends"></i> <?= $counts['following'] ?> Following</span> |
                    </div>
                    <?php if(isset($_SESSION['user-id']) && $_SESSION['user-id'] != $author_id): ?>
                        <button class="follow-btn <?= $is_following ? 'following' : '' ?>" 
                                data-author-id="<?= $author_id ?>">
                            <i class="uil <?= $is_following ? 'uil-user-check' : 'uil-user-plus' ?>"></i>
                            <?= $is_following ? 'Following' : 'Follow' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="author__posts">
            <h2>Latest Posts</h2>
            <div class="posts__container">
                <?php if(mysqli_num_rows($posts) > 0) : ?>
                    <?php while($post = mysqli_fetch_assoc($posts)) : ?>
                        <article class="post">
                            <div class="post__thumbnail">
                                <img src="<?= ROOT_URL . 'images/' . $post['thumbnail'] ?>">
                            </div>
                            <div class="post__info">
                                <h3 class="post__title">
                                    <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>"><?= $post['title'] ?></a>
                                </h3>
                                <p class="post__body"><?= substr($post['body'], 0, 150) ?>...</p>
                                <div class="post__author">
                                    <div class="post__author-info">
                                        <small><?= date("M d, Y - H:i", strtotime($post['date_time'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="alert__message error">
                        <p>No posts found for this author</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    die();
}

include 'partials/footer.php';
?>
