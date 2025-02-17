<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $author_id = $_SESSION['user-id'];
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $body = filter_var($_POST['body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category_id = filter_var($_POST['category'], FILTER_SANITIZE_NUMBER_INT);
    $is_featured = filter_var($_POST['is_featured'], FILTER_SANITIZE_NUMBER_INT);
    $thumbnail = $_FILES['thumbnail'];

    // is feutured

    $is_featured = $is_featured == 1 ?: 0;

    // validate data

    if (!$title) {
        $_SESSION['add-post'] = "Enter Post Title";
    } elseif (!$category_id) {
        $_SESSION['add-post'] = "Select category";
    } elseif (!$body) {
        $_SESSION['add-post'] = "Enter Post body";
    } elseif (!$thumbnail['name']) {
        $_SESSION['add-post'] = "Select Thumbnail for the Post";
    } else {
        //process $thumbnail
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
                $_SESSION['add-post'] = "File should be less than 2mb";
            }
        } else {
            $_SESSION['add-post'] = "File Should be png, jpg or jpeg";
        }
    }

    // Validate categories
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $categories = explode(',', $categories[0]); // Convert comma-separated string to array
    
    if(empty($categories)) {
        $_SESSION['add-post'] = "Please select at least one category";
    } else {
        // Continue with post creation if we have categories
        if(!isset($_SESSION['add-post'])) {
            // set is_featured of all posts to 0 and 1 for the featured one

            if ($is_featured == 1) {
                $zero_all_is_featured_query = "UPDATE posts SET is_featured=0";
                $zero_all_is_featured_result = mysqli_query($connection, $zero_all_is_featured_query);
            }
            //inset int db

            $query = "INSERT INTO posts (title, body, thumbnail, date_time, author_id, is_featured) VALUES ('$title', '$body', '$thumbnail_name', now(), $author_id, $is_featured)";
            $result = mysqli_query($connection, $query);

            if (mysqli_errno($connection)) {
                $_SESSION['add-post'] = "Couldn't create post";
            } else {
                $post_id = mysqli_insert_id($connection);
                // Insert categories
                foreach($categories as $category_id) {
                    if(!empty($category_id)) {
                        $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
                        $query = "INSERT INTO post_categories (post_id, category_id) 
                                VALUES ($post_id, $category_id)";
                        mysqli_query($connection, $query);
                    }
                }
                $_SESSION['add-post-success'] = "New post added successfully";
                header('location: ' . ROOT_URL . 'admin/');
                die();
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



// what i added 
// ALTER TABLE posts ADD CONSTRAINT FK_blog_category FOREIGN KEY(category_id) REFERENCES categories(id)ON DELETE SET NULL;

// ALTER TABLE posts ADD CONSTRAINT FK_blog_author FOREIGN KEY(author_id)REFERENCES users (id) ON DELETE CASCADE;
