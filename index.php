<?php
require 'partials/header.php';
require 'helpers/format_time.php';

// Add this function at the top of the file after requires
function isTrending($post) {
    $recent_threshold = strtotime('-24 hours');
    $post_date = strtotime($post['date_time']);
    $is_recent = $post_date >= $recent_threshold;
    
    return $is_recent && (
        ($post['repost_count'] >= 3) || 
        ($post['likes_count'] >= 5) || 
        ($post['comment_count'] >= 3)
    );
}

// is_featured post
$featured_query = "SELECT * FROM posts WHERE is_featured=1";
$featured_result = mysqli_query($connection, $featured_query);
$featured = mysqli_fetch_assoc($featured_result);

// Add this before the main query
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'trending';

$order_by = match($sort) {
    'recent' => "ORDER BY p.date_time DESC",
    'discussed' => "ORDER BY comment_count DESC, p.date_time DESC",
    'popular' => "ORDER BY repost_count DESC, likes_count DESC",
    'trending' => "ORDER BY 
        CASE 
            WHEN p.date_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN repost_count * 2 + likes_count
            WHEN p.date_time >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN repost_count + likes_count
            ELSE repost_count + likes_count - (DATEDIFF(NOW(), p.date_time) / 7)
        END DESC",
    default => "ORDER BY repost_count DESC, likes_count DESC, p.date_time DESC"
};

// retrive the posts from db 
$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0;
$query = "SELECT p.*,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'like') as likes_count,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            (SELECT type FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
          FROM posts p 
          $order_by
          LIMIT 9";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);
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


    <div class="posts__filter">
    <select id="sortPosts" onchange="changeSortOrder(this.value)">
        <option value="trending">Trending</option>
        <option value="popular">Most Popular</option>
        <option value="recent">Most Recent</option>
        <option value="discussed">Most Discussed</option>
    </select>
</div>

<script>
function changeSortOrder(order) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', order);
    window.location.href = currentUrl.toString();
}

// Set the selected option based on URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sort = urlParams.get('sort') || 'popular';
    document.getElementById('sortPosts').value = sort;
});
</script>

        <?php while ($post = mysqli_fetch_assoc($posts)) : ?>

            <?php
$is_popular = ($post['repost_count'] >= 5) || ($post['likes_count'] >= 10);
$is_trending = isTrending($post);
$thumbnail = $post['thumbnail'] ?? null;
?>
<article class="post <?= $thumbnail ? 'with-thumbnail' : 'no-thumbnail' ?> 
    <?= $is_popular ? 'popular' : '' ?> 
    <?= $is_trending ? 'trending' : '' ?>">

                <?php if ($thumbnail): ?>
                    <div class="post__thumbnail">
                        <img src="images/<?= $thumbnail ?>">
                    </div>
                <?php endif ?>
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
                        <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
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
                                <?php if (isset($_SESSION['user-id']) && $_SESSION['user-id'] == $author['id']): ?>
                                    <small><a href="<?= ROOT_URL ?>admin/profile.php">(View Profile)</a></small>
                                <?php endif; ?>
                            </h5>
                            <small title="<?= date("M d, Y - H:i", strtotime($post['date_time'])) ?>">
                                <?= timeAgo($post['date_time']) ?>
                            </small>
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
                        <!-- Repost chani chnau uko -->
                        <span class="interaction-item">

                        <script>
    function repostPost(postId) {
        if (!userId) {
            alert('Please log in to repost.');
            return;
        }

        fetch('repost-logic.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${postId}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing your request');
        });
    }
    </script>
                           <?php
                            // Check if user has reposted this post
                            $has_reposted = false;
                            if (isset($_SESSION['user-id'])) {
                                $repost_check_sql = "SELECT 1 FROM reposts WHERE user_id = ? AND post_id = ?";
                                $repost_check_stmt = $connection->prepare($repost_check_sql);
                                $repost_check_stmt->bind_param("ii", $_SESSION['user-id'], $post['id']);
                                $repost_check_stmt->execute();
                                $has_reposted = $repost_check_stmt->get_result()->num_rows > 0;
                            }

                            // Get the repost count
                            $count_sql = "SELECT COUNT(*) AS repost_count FROM reposts WHERE post_id = ?";
                            $count_stmt = $connection->prepare($count_sql);
                            $count_stmt->bind_param("i", $post['id']);
                            $count_stmt->execute();
                            $count_result = $count_stmt->get_result();
                            $repost_count = $count_result->fetch_assoc()['repost_count'];
                            ?>
                            <span class="interaction-item <?= $has_reposted ? 'active' : '' ?>">
                                <i class="fa fa-retweet" onclick="repostPost(<?= $post['id'] ?>)" 
                                style="<?= $has_reposted ? 'color: #6C63FF;' : '' ?>"></i>
                                <span class="interaction-count"><?= $repost_count ?></span>
                            </span>
                        </span>
                        <a href="<?= ROOT_URL ?>post.php?id=<?= $post['id'] ?>#comments-section">
                            <i class="fas fa-comment"></i>
                            <span><?= $post['comment_count'] ?></span>
                        </a>
                        <div class="reactions-summary" data-post-id="<?= $post['id'] ?>">
                           
                        </div>
                        
                    </div>
                    <?php
                            $recent_query = "SELECT u.firstname 
                                            FROM users u 
                                            JOIN post_reactions pr ON u.id = pr.user_id 
                                            WHERE pr.post_id = ? 
                                            ORDER BY pr.created_at DESC 
                                            LIMIT 2";
                            $stmt = mysqli_prepare($connection, $recent_query);
                            mysqli_stmt_bind_param($stmt, "i", $post['id']);
                            mysqli_stmt_execute($stmt);
                            $recent_result = mysqli_stmt_get_result($stmt);
                            $recent_users = [];
                            while ($user = mysqli_fetch_assoc($recent_result)) {
                                $recent_users[] = $user['firstname'];
                            }

                            $total_reactions = $post['likes_count'] + $post['dislikes_count'];
                            if ($total_reactions > 0) {
                                if (count($recent_users) > 0) {
                                    $others_count = $total_reactions - count($recent_users);
                                    $names = implode(' and ', $recent_users);
                                    if ($others_count > 0) {
                                        echo "<a href='" . ROOT_URL . "post-reactions.php?id=" . $post['id'] . "'>"
                                            . $names . " and " . $others_count . " other"
                                            . ($others_count > 1 ? "s" : "") . " reacted</a>";
                                    } else {
                                        echo "<a href='" . ROOT_URL . "post-reactions.php?id=" . $post['id'] . "'>"
                                            . $names . " reacted</a>";
                                    }
                                }
                            } else {
                                echo "<span>Be the first to react</span>";
                            }
                            ?>
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
        $all_categories = mysqli_query($connection, $all_categories_query);
        ?>

        <?php while ($category = mysqli_fetch_assoc($all_categories)) : ?>
            <a href="<?= ROOT_URL ?>category-posts.php?id=<?= $category['id'] ?>" class="category__button"><?= $category['title'] ?></a>
        <?php endwhile ?>
    </div>
</section>
<script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.userId = <?= isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 'null' ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= ROOT_URL ?>js/global.js"></script>
<script src="<?= ROOT_URL ?>js/interactions-likes.js"></script>

<?php include 'partials/footer.php'; ?>