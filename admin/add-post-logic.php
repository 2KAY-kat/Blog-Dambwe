<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $thumbnail = $_FILES['thumbnail'];

    // Validate basic post data
    if (!$title) {
        $_SESSION['add-post'] = "Enter Post Title";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Enter Post body";
    } elseif (!$thumbnail['name']) {
        $_SESSION['add-post'] = "Select Thumbnail for the Post";
    } else {
        // Process thumbnail
        $time = time();
        $thumbnail_name = $time . $thumbnail['name'];
        $thumbnail_tmp_name = $thumbnail['tmp_name'];
        $thumbnail_destination_path = '../images/' . $thumbnail_name;

        // Validate thumbnail
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = explode('.', $thumbnail_name);
        $extension = end($extension);
        
        if (!in_array($extension, $allowed_files)) {
            $_SESSION['add-post'] = "File Should be png, jpg or jpeg";
        } elseif ($thumbnail['size'] > 2000000) {
            $_SESSION['add-post'] = "File should be less than 2mb";
        } else {
            // Get categories
            $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
            if (is_string($categories[0])) {
                $categories = explode(',', $categories[0]);
            }

            if (empty($categories)) {
                $_SESSION['add-post'] = "Please select at least one category";
            } else {
                // Upload thumbnail
                move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);

                // Update featured status if necessary
                if ($is_featured) {
                    $zero_all_featured_query = "UPDATE posts SET is_featured=0";
                    mysqli_query($connection, $zero_all_featured_query);
                }

                // Create post
                $query = "INSERT INTO posts (title, body, thumbnail, date_time, author_id, is_featured) 
                         VALUES (?, ?, ?, NOW(), ?, ?)";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, 'sssii', $title, $body, $thumbnail_name, $author_id, $is_featured);
                
                if (mysqli_stmt_execute($stmt)) {
                    $post_id = mysqli_insert_id($connection);
                    
                    // Insert categories
                    foreach ($categories as $category_id) {
                        if (!empty($category_id)) {
                            $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
                            $cat_query = "INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)";
                            $cat_stmt = mysqli_prepare($connection, $cat_query);
                            mysqli_stmt_bind_param($cat_stmt, 'ii', $post_id, $category_id);
                            mysqli_stmt_execute($cat_stmt);
                        }
                    }
                    
                    $_SESSION['add-post-success'] = "New post added successfully";
                    header('location: ' . ROOT_URL . 'admin/');
                    die();
                } else {
                    $_SESSION['add-post'] = "Database error: " . mysqli_error($connection);
                }
            }
        }
    }

    if (isset($_SESSION['add-post'])) {
        $_SESSION['add-post-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/add-post.php');
        die();
    }
}

header('location: ' . ROOT_URL . 'admin/');
die();
