<?php
require_once __DIR__ . '/../config/database.php';

// current user in db

if(isset($_SESSION['user-id'])) {
    $id = filter_var($_SESSION['user-id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT avatar FROM users WHERE id=$id";
    $result = mysqli_query($connection, $query);
    $avatar = mysqli_fetch_assoc($result);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= ROOT_URL ?>partials/favicon.png" type="image/x-icon">
    <title>DEV.Query | HOME</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
    <!-- ICONSCOUT CDN -->
   <!-- <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
--> <!-- GOOGLE FONT (MONTSERRAT) -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/interactions.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/skeleton.css">
    <script>
        const ROOT_URL = '<?= ROOT_URL ?>';
    </script>
<script src="<?= ROOT_URL ?>js/notifications.js" defer></script>

<link rel="manifest" href="<?= ROOT_URL ?>manifest.json">

</head>
<body>
    <?php include 'skeleton-loader.php'; ?>
    <nav>
    <div class="container nav__container">
        <a href="<?= ROOT_URL ?>" class="nav__logo"><img src="<?= ROOT_URL ?>partials/favicon.ico" alt=""></a>
        <ul class="nav__items">
            <li><a href="<?= ROOT_URL ?>blog.php">Blog</a></li>
            <li><a href="<?= ROOT_URL ?>about.php">About</a></li>
            <li><a href="<?= ROOT_URL ?>services.php">Services</a></li>
            <li><a href="<?= ROOT_URL ?>contact.php">Contact</a></li>
            </ul>
        
        <div class="nav__buttons">
        <?php if(isset($_SESSION['user-id'])) : ?>
                <li class="nav__profile">
                    <div class="avatar">
                       <a href="<?= ROOT_URL ?>admin/profile.php"> <img src="<?= ROOT_URL . 'images/' . ($avatar['avatar'] ?: 'default-avatar.png') ?>"></a>
                    </div>
                    <ul>
                        <?php if(strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
                            <li><a href="<?= ROOT_URL ?>admin/profile.php">My Profile</a></li>
                            <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="<?= ROOT_URL ?>admin/index.php">Dashboard</a></li>
                            <li><a href="<?= ROOT_URL ?>admin/profile.php">View Profile</a></li>
                            <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <?php else : ?>
                <li><a class="signin-btn" href="<?= ROOT_URL ?>signin.php"><i class="fa fa-sign-in"></i> login</a></li>
                <li><a class="sign-up-btn" href="<?= ROOT_URL ?>signup.php"><i class="fa fa-sign-in"></i> Signup</a></li>
                <?php endif ?>



            <?php if(isset($_SESSION['user-id'])) : ?>
                <div class="notifications-icon">
                    <a href="<?= ROOT_URL ?>admin/notifications.php">
                        <i class="fa-regular fa-bell"></i>
                    </a>
                    <span class="notification-count"></span>
                </div>
            <?php endif; ?>
            <button id="open__nav-btn"><i class="fas fa-bars"></i></button>
            <button id="close__nav-btn"><i class="fas fa-times"></i></button>
        </div>
    </div>
</nav>
    <?php 
    if (!strpos($_SERVER['REQUEST_URI'], 'signin.php') && 
        !strpos($_SERVER['REQUEST_URI'], 'signup.php')) {
        // Use filesystem path for file inclusion thid helps since its more available in all directories and also its fucking secure thanks to @copilot
        require_once __DIR__ . '/../includes/breadcrumbs.php';
    }
    ?>
    <script src="<?= ROOT_URL ?>js/skeleton-loader.js"></script>
<!---  end nav   -->