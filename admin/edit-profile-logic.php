<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    $id = $_SESSION['user-id'];
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];
    $cover_photo = $_FILES['cover_photo'];

    // Validate inputs
    if(!$firstname || !$lastname) {
        $_SESSION['edit-profile'] = "Please enter both first and last name";
    } else {
        // Process avatar
        if($avatar['name']) {
            $time = time();
            $avatar_name = $time . $avatar['name'];
            $avatar_tmp_name = $avatar['tmp_name'];
            $avatar_destination_path = '../images/' . $avatar_name;

            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = explode('.', $avatar_name);
            $extension = end($extension);
            if(in_array($extension, $allowed_files)) {
                if($avatar['size'] < 2000000) {
                    move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                } else {
                    $_SESSION['edit-profile'] = "File size too big. Should be less than 2MB";
                }
            } else {
                $_SESSION['edit-profile'] = "File should be png, jpg, or jpeg";
            }
        }

        // Process cover photo
        if($cover_photo['name']) {
            $time = time();
            $cover_photo_name = 'cover_' . $time . $cover_photo['name'];
            $cover_photo_tmp_name = $cover_photo['tmp_name'];
            $cover_photo_destination_path = '../images/' . $cover_photo_name;

            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = explode('.', $cover_photo_name);
            $extension = end($extension);
            if(in_array($extension, $allowed_files)) {
                if($cover_photo['size'] < 2000000) {
                    move_uploaded_file($cover_photo_tmp_name, $cover_photo_destination_path);
                } else {
                    $_SESSION['edit-profile'] = "Cover photo file size too big. Should be less than 2MB";
                }
            } else {
                $_SESSION['edit-profile'] = "Cover photo file should be png, jpg, or jpeg";
            }
        }

        if(!isset($_SESSION['edit-profile'])) {
            $avatar_to_insert = $avatar_name ?? $avatar['avatar'];
            $cover_photo_to_insert = $cover_photo_name ?? $user['cover_photo'];

            // Update user
            $query = "UPDATE users SET firstname=?, lastname=?, bio=?, avatar=?, cover_photo=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'sssssi', $firstname, $lastname, $bio, $avatar_to_insert, $cover_photo_to_insert, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['edit-profile-success'] = "Profile updated successfully";
            }
        }
    }
}

header('location: ' . ROOT_URL . 'admin/profile.php');
die();
