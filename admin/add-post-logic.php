<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    if (is_string($categories)) {
        $categories = explode(',', $categories);
    }
    if (!is_array($categories)) {
        $categories = [$categories];
    }
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Validate form data
    if (!$title) {
        $_SESSION['add-post'] = "Enter post title";
    } elseif (empty($categories)) {
        $_SESSION['add-post'] = "Select at least one category";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Enter post body";
    } else {
        // Initialize thumbnail name as null
        $thumbnail_name = null;

        // Only process thumbnail if one was uploaded
        if ($thumbnail['size'] > 0) {
            $time = time();
            $thumbnail_name = $time . $thumbnail['name'];
            $thumbnail_tmp_name = $thumbnail['tmp_name'];
            $thumbnail_destination_path = '../images/' . $thumbnail_name;

            // Validate file
            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = explode('.', $thumbnail_name);
            $extension = end($extension);
            if (in_array($extension, $allowed_files)) {
                if ($thumbnail['size'] < 2000000) {
                    // Upload thumbnail
                    move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
                } else {
                    $_SESSION['add-post'] = "Couldn't upload image. File size too big. Should be less than 2mb";
                }
            } else {
                $_SESSION['add-post'] = "Couldn't upload image. File should be png, jpg, or jpeg";
            }
        }
    }

    // If there are no errors, save post to database
    if (!isset($_SESSION['add-post'])) {
        // Set all posts to not featured
        if ($is_featured) {
            $set_all_posts_not_featured_query = "UPDATE posts SET is_featured=0";
            $set_all_posts_not_featured_result = mysqli_query($connection, $set_all_posts_not_featured_query);
        }

        // Insert post into database
        $query = "INSERT INTO posts (title, body, thumbnail, date_time, author_id, is_featured) VALUES (?, ?, ?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'sssii', $title, $body, $thumbnail_name, $author_id, $is_featured);
        $result = mysqli_stmt_execute($stmt);

        if (!mysqli_errno($connection)) {
            // Get the post id
            $post_id = mysqli_insert_id($connection);

            // Insert categories using prepared statement
            $category_query = "INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)";
            $category_stmt = mysqli_prepare($connection, $category_query);
            foreach ($categories as $category_id) {
                mysqli_stmt_bind_param($category_stmt, 'ii', $post_id, $category_id);
                mysqli_stmt_execute($category_stmt);
            }

            $_SESSION['add-post-success'] = "New post added successfully";
        } else {
            $_SESSION['add-post'] = "Couldn't add post to database";
        }
    }

    header('location: ' . ROOT_URL . 'admin/index.php');
    die();
} else {
    header('location: ' . ROOT_URL . 'admin/add-post.php');
    die();
}
