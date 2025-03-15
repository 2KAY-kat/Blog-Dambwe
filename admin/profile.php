<?php
include 'partials/header.php';

$current_user_id = $_SESSION['user-id'];

// Check if tables exist first
$tables_check = mysqli_query($connection, "SHOW TABLES");
$existing_tables = [];
while ($table = mysqli_fetch_array($tables_check)) {
    $existing_tables[] = $table[0];
}

// Create user_profiles table if it doesn't exist
if (!in_array('user_profiles', $existing_tables)) {
    $create_profiles_table = "CREATE TABLE IF NOT EXISTS user_profiles (
        user_id INT PRIMARY KEY,
        theme_color VARCHAR(7) DEFAULT '#default',
        privacy_setting ENUM('public', 'private', 'followers') DEFAULT 'public',
        social_links JSON NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
    )";
    
    if (!mysqli_query($connection, $create_profiles_table)) {
        die("Error creating user_profiles table: " . mysqli_error($connection));
    }
    
    // Insert default profile for current user
    $insert_default = "INSERT IGNORE INTO user_profiles (user_id) VALUES (?)";
    $stmt = mysqli_prepare($connection, $insert_default);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
    }
}

// Fetch current user's data with privacy settings

// Check if required tables exist
$tables_check = mysqli_query($connection, "SHOW TABLES");
if (!$tables_check) {
    die("Error checking tables: " . mysqli_error($connection));
}

$existing_tables = [];
while ($table = mysqli_fetch_array($tables_check)) {
    $existing_tables[] = $table[0];
}

// Build query based on existing tables
$base_query = "SELECT u.*, COUNT(DISTINCT p.id) as post_count";
$joins = [];
$joins[] = "FROM users u";
$joins[] = "LEFT JOIN posts p ON u.id = p.author_id";

if (in_array('user_profiles', $existing_tables)) {
    $joins[] = "LEFT JOIN user_profiles up ON u.id = up.user_id";
    $base_query .= ", COALESCE(up.theme_color, '#default') as theme_color, 
                     COALESCE(up.privacy_setting, 'public') as privacy_setting, 
                     COALESCE(up.social_links, '{}') as social_links";
} else {
    $base_query .= ", '#default' as theme_color, 'public' as privacy_setting, '{}' as social_links";
}

if (in_array('followers', $existing_tables)) {
    $base_query .= ", COUNT(DISTINCT f1.follower_id) as followers_count, COUNT(DISTINCT f2.following_id) as following_count";
    $joins[] = "LEFT JOIN followers f1 ON u.id = f1.following_id";
    $joins[] = "LEFT JOIN followers f2 ON u.id = f2.follower_id";
}

if (in_array('user_badges', $existing_tables)) {
    $base_query .= ", b.badges";
    $joins[] = "LEFT JOIN user_badges b ON u.id = b.user_id";
}

$query = $base_query . " " . implode(" ", $joins) . " WHERE u.id = ? GROUP BY u.id";

// Prepare and execute statement with error handling
$stmt = mysqli_prepare($connection, $query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($connection));
}

if (!mysqli_stmt_bind_param($stmt, "i", $current_user_id)) {
    die("Error binding parameters: " . mysqli_stmt_error($stmt));
}

if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Error getting result: " . mysqli_stmt_error($stmt));
}

$user = mysqli_fetch_assoc($result);
if (!$user) {
    die("User not found");
}

// Set default values
$user = array_merge([
    'followers_count' => 0,
    'following_count' => 0,
    'badges' => '[]',
    'social_links' => '{}',
    'privacy_setting' => 'public',
    'theme_color' => '#default',
    'bio' => 'No bio yet.',
    'post_count' => 0
], $user);

// Fetch reactions if table exists
$reactions = [];
if (in_array('profile_reactions', $existing_tables)) {
    $reactions_query = "SELECT reaction_type, COUNT(*) as count 
                       FROM profile_reactions 
                       WHERE profile_id = ? 
                       GROUP BY reaction_type";
    $stmt = mysqli_prepare($connection, $reactions_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $current_user_id);
        mysqli_stmt_execute($stmt);
        $reactions_result = mysqli_stmt_get_result($stmt);
        if ($reactions_result) {
            $reactions = mysqli_fetch_all($reactions_result, MYSQLI_ASSOC);
        }
    }
}
?>

<section class="dashboard">
    <div class="container dashboard__container">
        <button id="show__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-right"></i></button>
        <button id="hide__sidebar-btn" class="sidebar__toggle"><i class="fas fa-chevron-left"></i></button>

        <aside>
            <ul>
                <li><a href="add-post.php"><i class="fas fa-pen"></i>
                    <h5>Add Post</h5>
                </a></li>
                <li><a href="<?= ROOT_URL ?>admin/index.php"><i class="fas fa-pencil"></i>
                    <h5>Manage Posts</h5>
                </a></li>
                <li><a href="edit-profile.php" class="active"><i class="fas fa-user-edit"></i>
                    <h5>Edit Profile</h5>
                </a></li>
                <li><a href="add-category.php"><i class="fas fa-edit"></i>
                    <h5>Add Category</h5>
                </a></li>
                <?php if(isset($_SESSION['user_is_admin']) && $_SESSION['user_is_admin']): ?>
                    <li><a href="add-user.php"><i class="fas fa-user-plus"></i>
                        <h5>Add User</h5>
                    </a></li>
                    <li><a href="manage-users.php"><i class="fas fa-user-cog"></i>
                        <h5>Manage Users</h5>
                    </a></li>
                    <li><a href="manage-categories.php"><i class="fas fa-list"></i>
                        <h5>Manage Categories</h5>
                    </a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <main>
            <?php if(isset($_SESSION['edit-profile-success'])) : ?>
                <div class="alert__message success">
                    <p>
                        <?= $_SESSION['edit-profile-success'];
                        unset($_SESSION['edit-profile-success']);
                        ?>
                    </p>
                </div>
            <?php endif ?>
            
            <div class="profile-header" style="background-color: <?= $user['theme_color'] ?? '#default' ?>; "> 
            <!--<div class="profile-header">-->
                <div class="profile-cover">
                    <img src="<?= ROOT_URL . 'images/' . ($user['cover_photo'] ?? 'default-cover.jpg') ?>" alt="Cover">
                    <?php if($current_user_id == $_SESSION['user-id']): ?>
                        <button class="edit-cover-btn"><a href="<?= ROOT_URL ?>admin/edit-profile.php"><i class="fas fa-camera"></i></a></button>
                    <?php endif; ?>
                </div>
               

                <div class="profile-info">
                    <div class="profile-avatar">
                        <img src="<?= ROOT_URL . 'images/' . ($user['avatar'] ?? 'default-avatar.png') ?>" alt="Avatar">
                        <?php if($current_user_id == $_SESSION['user-id']): ?>
                            
                            <button class="edit-avatar-btn"><a href="<?= ROOT_URL ?>admin/edit-profile.php"><i class="fas fa-camera"></i></a></button>
                    
                        <?php endif; ?>
                    </div>

                    <div class="profile-details">
                        <h1><?= $user['display_name'] ?? $user['firstname'] . ' ' . $user['lastname'] ?></h1>
                        <p class="username">@<?= $user['username'] ?></p>
                        
                        <?php if(!empty($user['badges'])): ?>
                            <div class="badges">
                                <?php foreach(json_decode($user['badges']) as $badge): ?>
                                    <span class="badge <?= $badge ?>"><?= ucfirst($badge) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <ul>
                            <li><strong>Email:</strong> <?= $user['email'] ?></li>
                            <li><strong>Joined:</strong> <?= date("M d, Y", strtotime($user['date_time'])) ?></li>
                        </ul>
                   
                        <div class="stats">
                            <div>Posts: <?= $user['post_count'] ?></div>
                            <div>Followers: <?= $user['followers_count'] ?></div>
                            <div>Following: <?= $user['following_count'] ?></div>
                        </div>

                        <?php if($current_user_id != $_SESSION['user-id']): ?>
                            <button class="follow-btn">Follow</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="profile-bio">
                    <h3>About</h3>
                    <p><?= $user['bio'] ?? 'No bio yet.' ?></p>

                    
                    
                    <?php if(!empty($user['social_links'])): ?>
                        <div class="social-links">
                            <?php foreach(json_decode($user['social_links'], true) as $platform => $link): ?>
                                <a href="<?= $link ?>" target="_blank"><i class="fab fa-<?= $platform ?>"></i></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-reactions">
                    <h3>Profile Reactions</h3>
                    <div class="reactions-container">
                        <?php foreach($reactions as $reaction): ?>
                            <div class="reaction">
                                <span><?= $reaction['reaction_type'] ?></span>
                                <span><?= $reaction['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</section>

<style>
/* Mobile-first styles */
.profile-header {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    background-color: var(--color-primary);
}

.profile-cover {
    position: relative;
    height: 150px;
    overflow: hidden;
    border-radius: 10px;
}

.profile-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar {
    position: relative;
    width: 100px;
    height: 100px;
    margin: -50px auto 0;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid #fff;
}

.profile-details {
    text-align: center;
    padding: 1rem;
}

.badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
}

.stats {
    display: flex;
    justify-content: space-around;
    margin: 1rem 0;
}

/* Tablet and above */
@media (min-width: 768px) {
    .profile-avatar {
        margin: -50px 0 2rem;
    }

    .profile-details {
        text-align: left;
        margin-left: 150px;
    }

    .badges {
        justify-content: flex-start;
    }
}
</style>
<script src="<?= ROOT_URL ?>js/profile.js" defer></script>
<?php include '../partials/footer-auth.php'; ?>