<?php
require 'config/database.php';

if (isset($_POST['post_id']) && isset($_POST['comment_body'])) {
    $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
    $comment_text = filter_var($_POST['comment_body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = $_SESSION['user-id'];
    $parent_id = !empty($_POST['parent_id']) ? filter_var($_POST['parent_id'], FILTER_SANITIZE_NUMBER_INT) : null;

    if (!$comment_text) {
        $_SESSION['add-comment'] = "Please enter a comment";
    } else {
        // Use comment_text instead of body to match your database structure
        $query = "INSERT INTO comments (post_id, user_id, comment_text, parent_id, date_time) 
                 VALUES (?, ?, ?, ?, NOW())";
                 
        $stmt = mysqli_prepare($connection, $query);
        
        if ($stmt === false) {
            $_SESSION['add-comment'] = "Prepare failed: " . mysqli_error($connection);
        } else {
            mysqli_stmt_bind_param($stmt, "iisi", $post_id, $user_id, $comment_text, $parent_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['add-comment-success'] = "Comment added successfully";
            } else {
                $_SESSION['add-comment'] = "Execute failed: " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

header('location: ' . $_SERVER['HTTP_REFERER']);
die();
