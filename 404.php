<?php
require 'config/constants.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= ROOT_URL ?>partials/favicon.ico" type="image/x-icon">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/404.css">
    <link rel="stylesheet" type="text/css" href="<?= ROOT_URL ?>css/fontawesome-free-6.6.0-web/fontawesome-free-6.6.0-web/css/all.css">
</head>

<body>
    <div class="container">
        <svg class="illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#214b77">
            <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zM10.5 18h-3v-3h3v3zm0-5h-3V7h3v6z" />
        </svg>

        <h6>Error 404 <br> Page Not Found</h6>


        <p>
           You Must be lost or the page you are trying to access is under development
        </p>

        <a href="<?= ROOT_URL ?>index.php">
            <i class="fa fa-arrow-left"></i>
            Go Back
        </a>

    </div>
</body>

</html>