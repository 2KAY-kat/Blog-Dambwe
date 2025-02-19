<?php
require 'config/constants.php';

// Destroy all sessions
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
</head>
<body>
    <section class="empty__page">
        <h1>You are now logged out</h1>
        <p>
            <a href="<?= ROOT_URL ?>signin.php">Login</a> or <a href="<?= ROOT_URL ?>signup.php">Signup</a>
        </p>
    </section>
</body>
</html>