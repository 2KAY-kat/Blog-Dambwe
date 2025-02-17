<?php
require 'config/constants.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dambwe Blog | SIGN IN | SIGN UP</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style-background.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>


<section class="form__section">
    <div class="container form__section-container">
        <h2>You have logged out of your acount</h2>
        <div class="out">
            <small>Dont have an account? <a href="signup.php">Sign Up</a></small>

            <small>Already have account? <a href="signin.php">Sign In</a></small>
        </div>
        </form>
    </div>
    
</section>
<?php
include 'partials/footer-auth.php';
?>
</body>
</html>