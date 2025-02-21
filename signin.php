<?php
require 'config/database.php'; // This now includes session handling

// Clear signup/signin data if user is already logged in
if (isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL);
    exit;
}

$username_email = $_SESSION['signin_data']['username_email'] ?? null;
$password = $_SESSION['signin_data']['password'] ?? null;

unset($_SESSION['signin_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dambwe Blog | SIGN  IN</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style-background.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>


<section class="form__section">
    <div class="container form__section-container">
        <h2>Sign In</h2>
        <?php if(isset($_SESSION['signup-success'])) : ?>

            <div class="alert__message success">
            <p>
                <?= $_SESSION['signup-success'];
                unset($_SESSION['signup-success']);
                ?>
            </p>
        </div>

        <?php elseif(isset($_SESSION['signin'])) : ?>

        <div class="alert__message error">
            <p>
                <?= $_SESSION['signin'];
                unset($_SESSION['signin']);
                ?>
            </p>
        </div>
        <?php endif ?>
        <form action="<?= ROOT_URL ?>signin-logic.php" method="post">
            <input type="text" name="username_email" value="<?= $username_email ?>" placeholder="Username or Email">
            <input type="password" name="password" value="<?= $password ?>" placeholder="Password">

            <button class="btn" type="submit" name="submit">Sign In</button>
            <small>Dont have an account? <a href="signup.php">Sign Up</a></small>
        </form>
    </div>
</section>
<?php
include 'partials/footer-auth.php';
?>
</body>
</html>