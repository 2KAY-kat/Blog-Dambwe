<?php
require '../config/database.php';

if(!isset($_SESSION['user-id'])) {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}

// Get current user's admin status
$current_user_id = $_SESSION['user-id'];
$query = "SELECT * FROM users WHERE id=$current_user_id";
$result = mysqli_query($connection, $query);
$current_user = mysqli_fetch_assoc($result);
$_SESSION['user_is_admin'] = $current_user['is_admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Dashboard</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <div class="container nav__container">
        <a href="<?= ROOT_URL ?>" class="nav__logo"><img src="<?= ROOT_URL ?>partials/favicon.ico" alt=""></a>
            <ul class="nav__items">
                <li><a href="<?= ROOT_URL ?>blog.php">Blog</a></li>
                <li><a href="<?= ROOT_URL ?>about.php">About</a></li>
                <li><a href="<?= ROOT_URL ?>services.php">Services</a></li>
                <li><a href="<?= ROOT_URL ?>contact.php">Contact</a></li>
                <?php if(isset($_SESSION['user-id'])) : ?>
                <li class="nav__profile">
                    <div class="avatar">
                        <img src="<?= ROOT_URL . 'images/' . $current_user['avatar'] ?>">
                    </div>
                    <ul>
                        <li><a href="<?= ROOT_URL ?>admin/index.php">Dashboard</a></li>
                        <li><a href="<?= ROOT_URL ?>admin/profile.php">View Profile</a></li>
                        <li><a href="<?= ROOT_URL ?>logout.php">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
            <button id="open__nav-btn"><i class="fas fa-bars"></i></button>
            <button id="close__nav-btn"><i class="fas fa-times"></i></button>

        </div>
    </nav>
