<?php
require 'config/database.php';
require 'helpers/format_time.php';

//retrieve post using its id
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT p.*, 
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'like') as likes_count,
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'dislike') as dislikes_count,
              (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
              (SELECT type FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
              FROM posts p 
              WHERE p.id = ?";

    $stmt = mysqli_prepare($connection, $query);
    $user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0;
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
    
    if (!$post) {
        header('location: ' . ROOT_URL . 'blog.php');
        exit();
    }
} else {
    header('location: ' . ROOT_URL . 'blog.php');
    exit();
}

// Now include the header
include 'partials/header.php';
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

    <!-- Post Interactions Bar -->
    <div class="post__interactions-bar">
        <div class="interaction-item <?= $post['user_reaction'] === 'like' ? 'active' : '' ?>" 
             id="like-btn" 
             data-post-id="<?= $post['id'] ?>"
             data-action="like">
            <i class="fas fa-thumbs-up"></i>
            <span class="interaction-count"><?= $post['likes_count'] ?? 0 ?></span>
        </div>
        
        <div class="interaction-item <?= $post['user_reaction'] === 'dislike' ? 'active' : '' ?>" 
             id="dislike-btn" 
             data-post-id="<?= $post['id'] ?>"
             data-action="dislike">
            <i class="fas fa-thumbs-down"></i>
            <span class="interaction-count"><?= $post['dislikes_count'] ?? 0 ?></span>
        </div>
        
        <div class="interaction-item" id="comments-toggle">
            <i class="fas fa-comment"></i>
            <span class="interaction-count" id="comments-count"><?= $post['comments_count'] ?? 0 ?></span>
        </div>
        
        <div class="share-menu-wrapper">
            <div class="interaction-item" id="share-btn">
                <i class="fas fa-share-alt"></i>
                <span>Share</span>
            </div>
            <div class="share-options">
                <a href="#" onclick="shareOnFacebook()"><i class="fab fa-facebook"></i>Facebook</a>
                <a href="#" onclick="shareOnTwitter()"><i class="fab fa-twitter"></i>Twitter</a>
                <a href="#" onclick="shareOnWhatsApp()"><i class="fab fa-whatsapp"></i>WhatsApp</a>
                <a href="#" onclick="shareOnLinkedIn()"><i class="fab fa-linkedin"></i>LinkedIn</a>
                <a href="#" onclick="copyLink()"><i class="fas fa-link"></i>Copy Link</a>
            </div>
        </div>
    </div>

    <!-- Comments Section (Initially Hidden) -->
    <div id="comments-section" class="comments-section" style="display: none;">
        <h3>Comments (<?= $post['comments_count'] ?>)</h3>
        <?php if(isset($_SESSION['user-id'])): ?>
            <div class="comment-form-wrapper">
                <form class="comment-form" id="main-comment-form">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <input type="hidden" name="parent_id" value="0">
                    <textarea name="comment_text" placeholder="Write a comment..." required></textarea>
                    <div class="form-buttons">
                        <button type="submit" class="btn">Post Comment</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="sign-in-prompt">
                <p>Please <a href="signin.php">sign in</a> to join the discussion.</p>
            </div>
        <?php endif; ?>
        
        <div id="comments-container"></div>
        <div id="comments-loader" style="display: none;">
            <div class="loader"></div>
        </div>
    </div>

</div>

<div id="delete-modal" class="modal">
    <div class="modal-content">
        <h3>Delete Comment</h3>
        <p>Are you sure you want to delete this comment? This will also delete all replies to this comment.</p>
        <div class="modal-buttons">
            <button class="btn confirm-delete">Delete</button>
            <button class="btn cancel-delete">Cancel</button>
        </div>
    </div>
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

<!-- Scripts -->
<script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.postId = <?= $post['id'] ?>;
    window.userId = <?= isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 'null' ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= ROOT_URL ?>js/global.js"></script>
<script src="<?= ROOT_URL ?>js/comments.js"></script>
<script src="<?= ROOT_URL ?>js/interactions-likes.js"></script>
<script src="<?= ROOT_URL ?>js/social-share.js"></script>

<?php include 'partials/footer.php'; ?>