<?php

// Initialize session data if not already set
if (!isset($_SESSION['signup-data'])) {
    $_SESSION['signup-data'] = [
        'firstname' => null,
        'lastname' => null,
        'username' => null,
        'email' => null,
        'createpassword' => null,
        'confirmpassword' => null,
        'avatar' => null
    ];
}

// Get current step from session or default to 1
$step = $_SESSION['signup-step'] ?? 1;

// Get back form data if there was an error
$firstname = $_SESSION['signup-data']['firstname'] ?? null;
$lastname = $_SESSION['signup-data']['lastname'] ?? null;
$username = $_SESSION['signup-data']['username'] ?? null;
$email = $_SESSION['signup-data']['email'] ?? null;
$createpassword = $_SESSION['signup-data']['createpassword'] ?? null;
$confirmpassword = $_SESSION['signup-data']['confirmpassword'] ?? null;

?>

<section class="form__section">
    <div class="container form__section-container">
        <h2>Sign Up - Step <?= $step ?> of 3</h2>
        <?php if (isset($_SESSION['signup'])) : ?>
            <div class="alert__message error">
                <p>
                    <?= $_SESSION['signup'];
                    unset($_SESSION['signup']);
                    ?>
                </p>
            </div>
        <?php endif ?>

        <form action="<?= ROOT_URL ?>signup-logic.php" enctype="multipart/form-data" method="post">
            <?php if ($step == 1) : ?>
                <!-- Step 1: Name and Username -->
                <input type="text" name="firstname" value="<?= $firstname ?>" placeholder="First Name" required>
                <input type="text" name="lastname" value="<?= $lastname ?>" placeholder="Last Name" required>
                <input type="text" name="username" value="<?= $username ?>" placeholder="Username" required>
                <button type="submit" class="btn" name="next1">Next</button>
            <?php endif ?>

            <?php if ($step == 2) : ?>
                <!-- Step 2: Email and Password -->
                <input type="email" name="email" value="<?= $email ?>" placeholder="Email" required>
                <input type="password" name="createpassword" value="<?= $createpassword ?>" placeholder="Create Password" required>
                <input type="password" name="confirmpassword" value="<?= $confirmpassword ?>" placeholder="Confirm Password" required>
                <button type="submit" class="btn" name="next2">Next</button>
            <?php endif ?>

            <?php if ($step == 3) : ?>
                <!-- Step 3: Avatar Upload -->
                <div class="form__control">
                    <label for="avatar">User Avatar</label>
                    <div class="avatar-preview">
                        <img id="avatar-preview" src="<?= ROOT_URL ?>images/default-avatar.png" alt="Avatar Preview">
                    </div>
                    <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewImage(this)">
                    <small>You can leave this blank and update it later in your profile settings.</small>
                </div>
                <button type="submit" class="btn" name="submit">Sign Up</button>
            <?php endif ?>

            <?php if ($step < 3) : ?>
                <small>Already have an account? <a href="signin.php">Sign In</a></small>
            <?php endif ?>
        </form>
    </div>
</section>

<?php
include 'partials/footer-auth.php';
?>
<script src="scripts/main.js"></script>
<script>
function previewImage(input) {
    const preview = document.getElementById('avatar-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = "<?= ROOT_URL ?>images/default-avatar.png";
    }
}
</script>
</body>
</html>