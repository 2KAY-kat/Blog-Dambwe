<?php
require 'config/database.php';

// Set JSON header for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['post_id']) && isset($_POST['comment_body'])) {
        $post_id = filter_var($_POST['post_id'], FILTER_SANITIZE_NUMBER_INT);
        $comment_text = filter_var($_POST['comment_body'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $user_id = $_SESSION['user-id'];
        $parent_id = !empty($_POST['parent_id']) ? filter_var($_POST['parent_id'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (!$comment_text) {
            $_SESSION['add-comment'] = "Please enter a comment";
        } else {
            try {
                mysqli_begin_transaction($connection);

                // Insert comment
                $query = "INSERT INTO comments (comment_text, post_id, user_id, parent_id) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, "siis", $comment_text, $post_id, $user_id, $parent_id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to add comment");
                }

                $comment_id = mysqli_insert_id($connection);

                // Get post author and comment details
                $post_query = "SELECT p.id as post_id, p.title, p.author_id, 
                              u.firstname as author_firstname, u.lastname as author_lastname 
                              FROM posts p 
                              JOIN users u ON p.author_id = u.id 
                              WHERE p.id = ?";
                $stmt = mysqli_prepare($connection, $post_query);
                mysqli_stmt_bind_param($stmt, "i", $post_id);
                mysqli_stmt_execute($stmt);
                $post_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                // Create notification for post author if the commenter is not the post author
                if ($post_info['author_id'] != $user_id) {
                    $notification_query = "INSERT INTO notifications 
                        (recipient_id, sender_id, post_id, comment_id, type, message) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($connection, $notification_query);
                    $type = 'comment';
                    $message = "commented on your post \"" . $post_info['title'] . "\"";
                    mysqli_stmt_bind_param($stmt, "iiiiss", 
                        $post_info['author_id'],
                        $user_id,
                        $post_id,
                        $comment_id,
                        $type,
                        $message
                    );
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Failed to create notification");
                    }
                }

                // If this is a reply, notify the parent comment author
                if ($parent_id) {
                    $parent_query = "SELECT c.user_id, u.firstname, u.lastname 
                                   FROM comments c 
                                   JOIN users u ON c.user_id = u.id 
                                   WHERE c.id = ?";
                    $stmt = mysqli_prepare($connection, $parent_query);
                    mysqli_stmt_bind_param($stmt, "i", $parent_id);
                    mysqli_stmt_execute($stmt);
                    $parent_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                    if ($parent_info && $parent_info['user_id'] != $user_id) {
                        $notification_query = "INSERT INTO notifications 
                            (recipient_id, sender_id, post_id, comment_id, type, message) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($connection, $notification_query);
                        $type = 'reply';
                        $message = "replied to your comment on \"" . $post_info['title'] . "\"";
                        mysqli_stmt_bind_param($stmt, "iiiiss", 
                            $parent_info['user_id'],
                            $user_id,
                            $post_id,
                            $comment_id,
                            $type,
                            $message
                        );
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception("Failed to create reply notification");
                        }
                    }
                }

                mysqli_commit($connection);
                
                // If this is an AJAX request, return JSON response
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Comment added successfully',
                        'comment_id' => $comment_id,
                        'debug' => [
                            'notification_sent' => true,
                            'recipient_id' => $post_info['author_id'],
                            'sender_id' => $user_id
                        ]
                    ]);
                    exit();
                }
                
                // For non-AJAX requests, redirect back
                $_SESSION['success'] = "Comment added successfully";
                header('location: ' . $_SERVER['HTTP_REFERER']);
                exit();

            } catch (Exception $e) {
                mysqli_rollback($connection);
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }
        exit();
    }
}

// Only redirect if not AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    header('location: ' . $_SERVER['HTTP_REFERER']);
}
die();
