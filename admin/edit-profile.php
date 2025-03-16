<?php
require '../config/database.php';  // Include database first
include 'partials/header.php';    // Then include header

// Fetch current user's data
$current_user_id = $_SESSION['user-id'];
$query = "SELECT * FROM users WHERE id=$current_user_id";
$result = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($result);
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
            <div class="edit-profile">
                <div class="edit-profile__header">
                    <h2>Edit Profile</h2>
                    <button type="submit" form="profile-form" name="submit" class="btn save-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

                <?php if(isset($_SESSION['edit-profile'])) : ?>
                    <div class="alert__message error">
                        <p><?= $_SESSION['edit-profile']; unset($_SESSION['edit-profile']); ?></p>
                    </div>
                <?php endif ?>

                <div class="edit-profile__content">
                    <!-- Profile Preview Card -->
                    <div class="profile-preview-card">
                        <div class="cover-upload">
                            <img id="cover-preview" src="<?= ROOT_URL . 'images/' . ($user['cover_photo'] ?: 'default-cover.jpg') ?>" alt="Cover">
                            <label for="cover_photo" class="upload-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Change Cover</span>
                            </label>
                        </div>
                        
                        <div class="avatar-upload">
                            <img id="avatar-preview" src="<?= ROOT_URL . 'images/' . ($user['avatar'] ?: 'default-avatar.png') ?>" alt="Avatar">
                            <label for="avatar" class="upload-overlay">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>

                        <div class="preview-info">
                            <h3 class="preview-name"><?= $user['display_name'] ?? $user['firstname'] . ' ' . $user['lastname'] ?></h3>
                            <p class="preview-username">@<?= $user['username'] ?></p>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form id="profile-form" class="edit-profile-form" action="<?= ROOT_URL ?>admin/edit-profile-logic.php" enctype="multipart/form-data" method="POST">
                        <input type="file" name="avatar" id="avatar" accept="image/*" hidden>
                        <input type="file" name="cover_photo" id="cover_photo" accept="image/*" hidden>
                        
                        <div class="form-sections">
                            <!-- Basic Info Section -->
                            <div class="edit-section">
                                <div class="section-header">
                                    <i class="fas fa-user"></i>
                                    <h3>Basic Information</h3>
                                </div>
                                <div class="form-fields">
                                    <div class="form__control">
                                        <label for="display_name">Display Name</label>
                                        <input type="text" name="display_name" value="<?= $user['display_name'] ?? '' ?>" 
                                               placeholder="How you want to be known">
                                    </div>
                                    <div class="form__control">
                                        <label for="username">Username</label>
                                        <div class="username-input">
                                            <span>@</span>
                                            <input type="text" name="username" value="<?= $user['username'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <div class="form__control">
                                        <label for="bio">Bio</label>
                                        <textarea name="bio" placeholder="Tell us about yourself"><?= $user['bio'] ?? '' ?></textarea>
                                        <div class="bio-counter">0/160</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Theme Section -->
                            <div class="edit-section">
                                <div class="section-header">
                                    <i class="fas fa-paint-brush"></i>
                                    <h3>Appearance</h3>
                                </div>
                                <div class="theme-options">
                                    <?php
                                    $themes = [
                                        ['#1a1a1a', 'Dark'],
                                        ['#2c3e50', 'Navy'],
                                        ['#16a085', 'Emerald'],
                                        ['#8e44ad', 'Purple'],
                                        ['#c0392b', 'Ruby']
                                    ];
                                    foreach($themes as $theme): ?>
                                        <label class="theme-option">
                                            <input type="radio" name="theme_color" value="<?= $theme[0] ?>"
                                                <?= ($user['theme_color'] ?? '') == $theme[0] ? 'checked' : '' ?>>
                                            <span class="color-preview" style="background-color: <?= $theme[0] ?>"></span>
                                            <span class="theme-name"><?= $theme[1] ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Social Links Section -->
                            <div class="edit-section">
                                <div class="section-header">
                                    <i class="fas fa-link"></i>
                                    <h3>Social Links</h3>
                                </div>
                                <div class="social-links">
                                    <?php 
                                    $social_links = json_decode($user['social_links'] ?? '{}', true);
                                    $platforms = [
                                        'twitter' => ['Twitter', 'fab fa-twitter', '#1DA1F2'],
                                        'instagram' => ['Instagram', 'fab fa-instagram', '#E1306C'],
                                        'linkedin' => ['LinkedIn', 'fab fa-linkedin', '#0077B5'],
                                        'facebook' => ['Facebook', 'fab fa-facebook', '#4267B2'],
                                        'github' => ['GitHub', 'fab fa-github', '#333333']
                                    ];
                                    foreach($platforms as $platform => $info): ?>
                                        <div class="social-input-group" style="--platform-color: <?= $info[2] ?>">
                                            <i class="<?= $info[1] ?>"></i>
                                            <input type="url" name="social_links[<?= $platform ?>]"
                                                   value="<?= $social_links[$platform] ?? '' ?>"
                                                   placeholder="<?= $info[0] ?> URL">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Privacy Section -->
                            <div class="edit-section">
                                <div class="section-header">
                                    <i class="fas fa-shield-alt"></i>
                                    <h3>Privacy Settings</h3>
                                </div>
                                <div class="privacy-options">
                                    <?php 
                                    $privacy_settings = [
                                        'public' => ['Everyone', 'fas fa-globe'],
                                        'followers' => ['Followers Only', 'fas fa-users'],
                                        'private' => ['Only Me', 'fas fa-lock']
                                    ];
                                    foreach($privacy_settings as $value => $info): ?>
                                        <label class="privacy-option">
                                            <input type="radio" name="privacy_setting" value="<?= $value ?>"
                                                   <?= ($user['privacy_setting'] ?? 'public') == $value ? 'checked' : '' ?>>
                                            <span class="option-icon"><i class="<?= $info[1] ?>"></i></span>
                                            <div class="option-info">
                                                <strong><?= $info[0] ?></strong>
                                                <small>Who can see your profile</small>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</section>

<script>
function previewImage(input) {
    const preview = document.getElementById('avatar-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewCoverImage(input) {
    const preview = document.getElementById('cover-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Add tab switching functionality
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons and content
        document.querySelectorAll('.tab-btn, .tab-content').forEach(el => el.classList.remove('active'));
        
        // Add active class to clicked button and corresponding content
        button.classList.add('active');
        document.getElementById(button.dataset.tab).classList.add('active');
    });
});

// Add bio counter
const bioTextarea = document.querySelector('textarea[name="bio"]');
const bioCounter = document.querySelector('.bio-counter');

bioTextarea.addEventListener('input', function() {
    const count = this.value.length;
    bioCounter.textContent = `${count}/160`;
    bioCounter.style.color = count > 160 ? 'var(--red-color)' : 'var(--color-gray-300)';
});

// Real-time preview updates
document.querySelector('input[name="display_name"]').addEventListener('input', function() {
    document.querySelector('.preview-name').textContent = this.value || 'Display Name';
});

document.querySelector('input[name="username"]').addEventListener('input', function() {
    document.querySelector('.preview-username').textContent = '@' + this.value;
});

// Theme color preview
document.querySelectorAll('input[name="theme_color"]').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelector('.profile-preview-card').style.backgroundColor = this.value;
    });
});
</script>

<style>
.form__section {
    background: var(--color-primary-light);
    padding: 1rem;
    border-radius: var(--card-border-radius);
    margin-bottom: 1rem;
}

.form__section h3 {
    margin-bottom: 1rem;
    color: var(--color-primary);
}

@media (max-width: 600px) {
    .form__section {
        padding: 0.5rem;
    }
}

/* Add this to your existing styles */
.edit-profile-tabs {
    background: var(--color-gray-900);
    border-radius: var(--card-boder-radius-3);
    padding: 2rem;
}

.tabs-nav {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--color-gray-700);
    padding-bottom: 1rem;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.5rem;
    background: var(--color-bg);
    border: none;
    border-radius: var(--card-boder-radius-2);
    color: var(--color-gray-300);
    cursor: pointer;
    transition: var(--transition);
}

.tab-btn:hover {
    background: var(--color-gray-700);
    color: var(--color-white);
}

.tab-btn.active {
    background: var(--color-primary);
    color: var(--color-white);
}

.tab-btn i {
    font-size: 1.2rem;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Media uploads styling */
.media-uploads {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.upload-group {
    text-align: center;
}

/* Social links styling */
.social-input {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: var(--color-bg);
    padding: 0.8rem;
    border-radius: var(--card-boder-radius-2);
    margin-bottom: 1rem;
}

.social-input i {
    font-size: 1.5rem;
    color: var(--color-primary);
}

/* Privacy options styling */
.privacy-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.privacy-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-bg);
    border-radius: var(--card-boder-radius-2);
    cursor: pointer;
    transition: var(--transition);
}

.privacy-option:hover {
    background: var(--color-gray-700);
}

.privacy-option input[type="radio"] {
    display: none;
}

.privacy-option i {
    font-size: 1.2rem;
    color: var(--color-gray-300);
}

.privacy-option input[type="radio"]:checked + i {
    color: var(--color-primary);
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .tabs-nav {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .tab-btn {
        flex: 1;
        min-width: 120px;
    }
}

@media screen and (max-width: 600px) {
    .edit-profile-tabs {
        padding: 1rem;
    }
    
    .tab-btn span {
        display: none;
    }
    
    .tab-btn i {
        font-size: 1.5rem;
    }
    
    .media-uploads {
        grid-template-columns: 1fr;
    }
}

/* Modern Edit Profile Styles */
.edit-profile {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.edit-profile__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.save-btn {
    background: var(--color-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1.5rem;
    font-weight: 600;
}

.edit-profile__content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

.profile-preview-card {
    background: var(--color-gray-900);
    border-radius: var(--card-boder-radius-3);
    overflow: hidden;
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.cover-upload {
    height: 100px;
    position: relative;
}

.avatar-upload {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: -50px auto 0;
    position: relative;
    border: 4px solid var(--color-gray-900);
}

.upload-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
    cursor: pointer;
    color: var(--color-white);
}

.upload-overlay:hover {
    opacity: 1;
}

.preview-info {
    padding: 1rem;
    text-align: center;
}

.edit-section {
    background: var(--color-gray-900);
    border-radius: var(--card-boder-radius-3);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-gray-700);
}

.section-header i {
    font-size: 1.2rem;
    color: var(--color-primary);
}

.username-input {
    display: flex;
    align-items: center;
    background: var(--color-bg);
    border-radius: var(--card-boder-radius-2);
    padding: 0 1rem;
}

.username-input span {
    color: var(--color-gray-300);
}

.username-input input {
    background: transparent;
    border: none;
    padding: 0.8rem;
}

.bio-counter {
    text-align: right;
    font-size: 0.8rem;
    color: var(--color-gray-300);
    margin-top: 0.5rem;
}

.theme-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}

.theme-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 3px solid transparent;
    transition: var(--transition);
}

.theme-option input:checked + .color-preview {
    border-color: var(--color-primary);
}

.social-input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-bg);
    border-radius: var(--card-boder-radius-2);
    margin-bottom: 1rem;
    border-left: 4px solid var(--platform-color);
}

.social-input-group i {
    color: var(--platform-color);
    font-size: 1.2rem;
}

.privacy-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-bg);
    border-radius: var(--card-boder-radius-2);
    margin-bottom: 1rem;
    cursor: pointer;
    transition: var(--transition);
}

.privacy-option:hover {
    background: var(--color-gray-700);
}

.privacy-option input[type="radio"] {
    display: none;
}

.option-icon {
    width: 40px;
    height: 40px;
    background: var(--color-gray-900);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.privacy-option input:checked ~ .option-icon {
    background: var(--color-primary);
}

.option-info {
    display: flex;
    flex-direction: column;
}

.option-info small {
    color: var(--color-gray-300);
}

@media screen and (max-width: 1024px) {
    .edit-profile__content {
        grid-template-columns: 1fr;
    }

    .profile-preview-card {
        position: static;
    }
}

@media screen and (max-width: 600px) {
    .edit-profile {
        padding: 1rem;
    }

    .theme-options {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>


<script src="<?= ROOT_URL ?>js/profile.js" defer></script>
<?php
include '../partials/footer-auth.php';
?>