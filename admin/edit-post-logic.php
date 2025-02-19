<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $categories = isset($_POST['categories']) ? explode(',', $_POST['categories']) : [];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $previous_thumbnail_name = filter_var($_POST['previous_thumbnail'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $thumbnail = $_FILES['thumbnail'];

    // Validate form data
    if (!$title) {
        $_SESSION['edit-post'] = "Enter post title";
    } elseif (!$categories) {
        $_SESSION['edit-post'] = "Select at least one category";
    } elseif (!$body) {
        $_SESSION['edit-post'] = "Enter post body";
    } else {
        // Delete existing post categories
        $delete_existing_categories_query = "DELETE FROM post_categories WHERE post_id = $id";
        $delete_existing_categories_result = mysqli_query($connection, $delete_existing_categories_query);

        // Process thumbnail if a new one was uploaded
        if ($thumbnail['name']) {
            // Delete previous thumbnail
            $previous_thumbnail_path = '../images/' . $previous_thumbnail_name;
            if ($previous_thumbnail_path) {
                unlink($previous_thumbnail_path);
            }

            // Work on new thumbnail
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
                    $_SESSION['edit-post'] = "Couldn't upload image. File size too big. Should be less than 2mb";
                }
            } else {
                $_SESSION['edit-post'] = "Couldn't upload image. File should be png, jpg, or jpeg";
            }
        }

        // If there are no errors, update post
        if (!isset($_SESSION['edit-post'])) {
            $thumbnail_to_insert = $thumbnail_name ?? $previous_thumbnail_name;

            // Set all posts to not featured
            if ($is_featured) {
                $set_all_posts_not_featured_query = "UPDATE posts SET is_featured=0";
                $set_all_posts_not_featured_result = mysqli_query($connection, $set_all_posts_not_featured_query);
            }

            // Update post
            $query = "UPDATE posts SET title='$title', body='$body', thumbnail='$thumbnail_to_insert', is_featured=$is_featured WHERE id=$id LIMIT 1";
            $result = mysqli_query($connection, $query);

            if (!mysqli_errno($connection)) {
                // Insert new categories
                if (!empty($categories)) {  // Check if categories array is not empty
                    foreach ($categories as $category_id) {
                        $insert_category_query = "INSERT INTO post_categories (post_id, category_id) VALUES ($id, $category_id)";
                        mysqli_query($connection, $insert_category_query);
                    }
                }

                $_SESSION['edit-post-success'] = "Post updated successfully";
            } else {
                $_SESSION['edit-post'] = "Failed to update post";
            }
        }
    }

    if (isset($_SESSION['edit-post'])) {
        // Redirect back to manage form page with form data
        header('location: ' . ROOT_URL . 'admin/edit-post.php?id=' . $id);
        die();
    } else {
        // Redirect back to manage posts page if the process was successful
        header('location: ' . ROOT_URL . 'admin/index.php');
        die();
    }
}

header('location: ' . ROOT_URL . 'admin/index.php');
die();