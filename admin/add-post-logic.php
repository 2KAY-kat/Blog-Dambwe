<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $categories = isset($_POST['categories']) ? explode(',', $_POST['categories']) : [];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Validate form data
    if (!$title) {
        $_SESSION['add-post'] = "Enter post title";
    } elseif (!$categories) {
        $_SESSION['add-post'] = "Select at least one category";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Enter post body";
    } elseif (!$thumbnail['name']) {
        $_SESSION['add-post'] = "Choose post thumbnail";
    } else {
        // Work on thumbnail
        $time = time();
        $thumbnail_name = $time . $thumbnail['name'];
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../images/' . $thumbnail_name;

        // Validate file
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = explode('.', $thumbnail_name);
        $extension = end($extension);
        if (in_array($extension, $allowed_files)) {
            // Make sure file is not too large (2mb)
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

    // If there are no errors, save post to database
    if (!isset($_SESSION['add-post'])) {
        // Set all posts to not featured
        if ($is_featured) {
            $set_all_posts_not_featured_query = "UPDATE posts SET is_featured=0";
            $set_all_posts_not_featured_result = mysqli_query($connection, $set_all_posts_not_featured_query);
        }

        // Insert post into database
        $query = "INSERT INTO posts (title, body, thumbnail, date_time, author_id, is_featured) VALUES ('$title', '$body', '$thumbnail_name', NOW(), $author_id, $is_featured)";
        $result = mysqli_query($connection, $query);

        if (!mysqli_errno($connection)) {
            // Get the post id
            $post_id = mysqli_insert_id($connection);

            // Insert categories
            foreach ($categories as $category_id) {
                $insert_category_query = "INSERT INTO post_categories (post_id, category_id) VALUES ($post_id, $category_id)";
                mysqli_query($connection, $insert_category_query);
            }

            $_SESSION['add-post-success'] = "New post added successfully";
        } else {
            $_SESSION['add-post'] = "Couldn't add post to database";
        }
    }
}

header('location: ' . ROOT_URL . 'admin/index.php');
die();
