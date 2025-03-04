<?php
include 'partials/header.php';

// Redirect non-admin users
if(!$_SESSION['user_is_admin']) {
    header('location: ' . ROOT_URL . 'admin/');
    die();
}

// get data if any error
$firstname = $_SESSION['add-user-data']['firstname'] ?? null;
$lastname = $_SESSION['add-user-data']['lastname'] ?? null;
$username = $_SESSION['add-user-data']['username'] ?? null;
$email = $_SESSION['add-user-data']['email'] ?? null;
$userrole = $_SESSION['add-user-data']['userrole'] ?? null;
$createpassword = $_SESSION['add-user-data']['createpassword'] ?? null;
$confirmpassword = $_SESSION['add-user-data']['confirmpassword'] ?? null;


// might delete later
unset($_SESSION['add-user-data']);
?>


<section class="form__section">
    <div class="container form__section-container">
        <h2>Add User</h2>
      <?php if(isset($_SESSION['add-user'])) : ?>

        <div class="alert__message error">
            <p>
                <?= $_SESSION['add-user'];
                unset($_SESSION['add-user']); ?>
            </p>
        </div>

        <?php endif ?>
        <form action="<?= ROOT_URL ?>admin/add-user-logic.php" enctype="multipart/form-data" method="post">
            <input type="text" name="firstname" value="<?= $firstname ?>" placeholder="First Name">
            <input type="text" name="lastname" value="<?= $lastname ?>" placeholder="Last Name">
            <input type="text" name="username" value="<?= $username ?>" placeholder="Username">
            <input type="email" name="email" value="<?= $email ?>" placeholder="Email">
            <input type="password" name="createpassword" value="<?= $createpassword ?>" placeholder="Create Password">
            <input type="password" name="confirmpassword" value="<?= $confirmpassword ?>" placeholder="Confirm Password">

            <select name="userrole">
                <option value="0">Author</option>
                <option value="1">Admin</option>
            </select>


            <div class="form__control">
                <label for="avatar">Add Avatar</label>
                <div class="avatar-preview">
                    <img id="avatar-preview" src="<?= ROOT_URL ?>images/default-avatar.png" alt="Avatar Preview">
                </div>
                <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewImage(this)">
                <small>You can leave this blank and update it later in your profile settings.</small>
            </div>

            <button class="btn" name="submit" type="submit">Add User</button>
        </form>
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
    } else {
        preview.src = "<?= ROOT_URL ?>images/default-avatar.png";
    }
}
</script>

<?php
include '../partials/footer-auth.php';
?>