<?php 
require 'config/database.php';

if(isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $previous_thumbnail = filter_var($_POST['previous_thumbnail'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_featured = filter_var($_POST['is_featured'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    // Get categories array
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    if (isset($categories[0])) {
        $categories = explode(',', $categories[0]);
    }

    // Validate input
    if(!$title || !$body) {
        $_SESSION['edit-post'] = "Couldn't update post. Invalid form data.";
    } elseif (empty($categories)) {
        $_SESSION['edit-post'] = "Please select at least one category";
    } else {
        // Handle new thumbnail if uploaded
        $thumbnail_name = $previous_thumbnail;
        if($thumbnail['name']) {
            $previous_thumbnail_path = '../images/' . $previous_thumbnail;
            if ($previous_thumbnail_path) {
                unlink($previous_thumbnail_path);
            }

            // process new photo by renaming and editing to dir

            $time = time();
            $thumbnail_name = $time . $thumbnail['name'];
            $thumbnail_tmp_name = $thumbnail['tmp_name'];
            $thumbnail_destination_path = '../images/' . $thumbnail_name;

            // safe checking

            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extention = explode('.', $thumbnail_name);
            $extention = end($extention);
            if (in_array($extention, $allowed_files)) {
                // image size no more than(2mb+)
                if ($thumbnail['size'] < 2_000_000) {
                    // upload the thumnail

                    move_uploaded_file($thumbnail_tmp_name, $thumbnail_destination_path);
                } else {
                    $_SESSION['edit-post'] = "File should be less than 2mb";
                }
            } else {
                $_SESSION['edit-post'] = "File Should be png, jpg or jpeg";
            }       
        }

        if(!isset($_SESSION['edit-post'])) {
            // Update featured status if needed
            if($is_featured) {
                $zero_all_is_featured_query = "UPDATE posts SET is_featured=0";
                mysqli_query($connection, $zero_all_is_featured_query);
            }

            // Update post
            $query = "UPDATE posts SET title=?, body=?, thumbnail=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $title, $body, $thumbnail_name, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                // Delete existing category relationships
                $delete_cats = "DELETE FROM post_categories WHERE post_id = ?";
                $delete_stmt = mysqli_prepare($connection, $delete_cats);
                mysqli_stmt_bind_param($delete_stmt, 'i', $id);
                mysqli_stmt_execute($delete_stmt);
                
                // Insert new categories
                $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
                if(is_string($categories[0])) {
                    $categories = explode(',', $categories[0]);
                }
                
                foreach($categories as $category_id) {
                    if(!empty($category_id)) {
                        $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
                        $cat_query = "INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)";
                        $cat_stmt = mysqli_prepare($connection, $cat_query);
                        mysqli_stmt_bind_param($cat_stmt, 'ii', $id, $category_id);
                        mysqli_stmt_execute($cat_stmt);
                    }
                }
                
                $_SESSION['edit-post-success'] = "Post updated successfully";
            } else {
                $_SESSION['edit-post'] = "Failed to update post";
            }
        }
    }

    if(isset($_SESSION['edit-post'])) {
        header('location: ' . ROOT_URL . 'admin/edit-post.php?id=' . $id);
    } else {
        header('location: ' . ROOT_URL . 'admin/');
    }
    die();
}

header('location: ' . ROOT_URL . 'admin/');
die();