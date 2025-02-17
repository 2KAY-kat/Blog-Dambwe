<?php
require 'config/database.php';


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
    <link rel="shortcut icon" href="<?= ROOT_URL ?>partials/favicon.ico" type="image/x-icon">
    <title>DEV.Query | HOME</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <nav>
        <div class="container nav__container">
            <a href="<?= ROOT_URL ?>" class="nav__logo"><img src="<?= ROOT_URL ?>partials/favicon.ico" alt=""></a>

           <?php /* <a href="<?= ROOT_URL ?>" class="nav__logo"><img src="<?= ROOT_URL ?>web-assets/storymat3_logo_&-icon-pack_20240926_133835_0000.png" alt=""></a> */?>
            <ul class="nav__items">
                <li><a href="<?= ROOT_URL ?>blog.php">Blog</a></li>
                <li><a href="<?= ROOT_URL ?>about.php">About</a></li>
                <li><a href="<?= ROOT_URL ?>services.php">Services</a></li>
                <li><a href="<?= ROOT_URL ?>contact.php">Contact</a></li>
                <?php if(isset($_SESSION['user-id'])) : ?>
                    <li class="nav__profile">
                    <div class="avatar">
                    <img src="<?= ROOT_URL . 'images/' . $avatar['avatar'] ?>">
                    </div>
                    <ul>
                        <?php if(strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
                            <!-- Admin section dropdown -->
                            <li><a href="<?= ROOT_URL ?>admin/profile.php">My Profile</a></li>
                            <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
                        <?php else: ?>
                            <!-- Main site dropdown -->
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
            </ul>

            <button id="open__nav-btn"><i class="fas fa-bars"></i></button>
            <button id="close__nav-btn"><i class="fas fa-times"></i></button>

        </div>
    </nav>


<!---  end nav   -->