<?php
include 'partials/header.php';
require 'helpers/format_time.php';

// retrive the posts from db all of them

$user_id = isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 0;
$query = "SELECT p.*,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'like') as likes_count,
            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND type = 'dislike') as dislikes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
            (SELECT type FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
          FROM posts p ORDER BY date_time DESC";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);

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

            <?php
                $thumbnail = $post['thumbnail'];
                ?>

            <article class="post <?= $thumbnail ? 'with-thumbnail' : 'no-thumbnail' ?>">
              
            <?php if ($thumbnail): ?>
            <div class="post__thumbnail">
                    <img src="images/<?= $post['thumbnail'] ?>">
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

                    <div class="reactions-summary" data-post-id="<?= $post['id'] ?>">
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


<!-- Add this before including interactions-likes.js -->
<script>
    window.ROOT_URL = '<?= ROOT_URL ?>';
    window.userId = <?= isset($_SESSION['user-id']) ? $_SESSION['user-id'] : 'null' ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= ROOT_URL ?>js/global.js"></script>
<script src="<?= ROOT_URL ?>js/interactions-likes.js"></script>

<?php include 'partials/footer.php'; ?>




<!-----

# =================================================================
# Local Development Settings (WAMP/XAMPP)
# =================================================================
RewriteEngine On
RewriteBase /Blog-Dambwe/

# Allow direct access to the api directory
RewriteRule ^api/ - [L]

# Handle other routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ $1.php [L]

# Custom error pages
ErrorDocument 404 /Blog-Dambwe/404.php
ErrorDocument 500 /Blog-Dambwe/500.php

# =================================================================
# Production Server Settings (Commented out by default)
# Uncomment these when uploading to production
# =================================================================

# Remove /Blog-Dambwe from path
#RewriteEngine On
#RewriteBase /

# Force HTTPS
#RewriteCond %{HTTPS} off
#RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove www from URL
#RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
#RewriteRule ^(.*)$ https://%1/$1 [R=301,L]

# Allow direct access to the api directory
#RewriteRule ^api/ - [L]

# Handle other routes
#RewriteCond %{REQUEST_FILENAME} !-f
#RewriteCond %{REQUEST_FILENAME} !-d
#RewriteRule ^(.*)$ $1.php [L]

# Custom error pages
#ErrorDocument 404 /404.php
#ErrorDocument 500 /500.php

# Prevent directory listing
#Options -Indexes

# Prevent access to .htaccess
#<Files .htaccess>
#    Order allow,deny
#    Deny from all
#</Files>

# Prevent access to sensitive files
#<FilesMatch "^\.">
#    Order allow,deny
#    Deny from all
#</FilesMatch>

# Enable GZIP compression
#<IfModule mod_deflate.c>
#    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json
#</IfModule>

# Set browser caching
#<IfModule mod_expires.c>
#    ExpiresActive On
#    ExpiresByType image/jpg "access plus 1 year"
#    ExpiresByType image/jpeg "access plus 1 year"
#    ExpiresByType image/png "access plus 1 year"
#    ExpiresByType image/gif "access plus 1 year"
#    ExpiresByType text/css "access plus 1 month"
#    ExpiresByType application/javascript "access plus 1 month"
#</IfModule>

# PHP settings for production
#php_value upload_max_filesize 5M
#php_value post_max_size 6M
#php_value max_execution_time 60
#php_flag display_errors off
#php_value memory_limit 256M

----->