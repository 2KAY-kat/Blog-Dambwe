<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    $id = $_SESSION['user-id'];
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    // Validate inputs
    if(!$firstname || !$lastname) {
        $_SESSION['edit-profile'] = "Please enter both first and last name";
    } else {
        if($avatar['name']) {
            // Work with new image
            $time = time();
            $avatar_name = $time . $avatar['name'];
            $avatar_tmp_name = $avatar['tmp_name'];
            $avatar_destination_path = '../images/' . $avatar_name;

            // Validate file
            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extention = explode('.', $avatar_name);
            $extention = end($extention);
            if(in_array($extention, $allowed_files)) {
                // Make sure file is not too large (2MB)
                if($avatar['size'] < 2000000) {
                    // Upload avatar
                    move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                } else {
                    $_SESSION['edit-profile'] = "File size too big. Should be less than 2MB";
                }
            } else {
                $_SESSION['edit-profile'] = "File should be png, jpg, or jpeg";
            }
        }

        if(!isset($_SESSION['edit-profile'])) {
            $avatar_to_insert = $avatar_name ?? $avatar['avatar'];
            
            // Update user
            $query = "UPDATE users SET firstname=?, lastname=?, bio=?, avatar=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'ssssi', $firstname, $lastname, $bio, $avatar_to_insert, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['edit-profile-success'] = "Profile updated successfully";
            }
        }
    }
}

header('location: ' . ROOT_URL . 'admin/profile.php');
die();
