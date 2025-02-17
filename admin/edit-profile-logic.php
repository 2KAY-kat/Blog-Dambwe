<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    $id = $_SESSION['user-id'];
    
    // Get current user data
    $current_user_query = "SELECT * FROM users WHERE id=$id";
    $current_user_result = mysqli_query($connection, $current_user_query);
    $current_user = mysqli_fetch_assoc($current_user_result);

    // Get form data, using current values as defaults
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $current_user['firstname'];
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $current_user['lastname'];
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $current_user['bio'];
    $avatar_to_insert = $current_user['avatar']; // Default to current avatar

    // validate input values
    if(!$firstname || !$lastname) {
        $_SESSION['edit-profile'] = "Please enter both first and last name";
    } else {
        // Update avatar only if new one is uploaded
        if($_FILES['avatar']['name']) {
            $avatar = $_FILES['avatar'];
            $time = time();
            $avatar_name = $time . $avatar['name'];
            $avatar_tmp_name = $avatar['tmp_name'];
            $avatar_destination_path = '../images/' . $avatar_name;

            // make sure file is an image
            $allowed_files = ['png', 'jpg', 'jpeg'];
            $extension = explode('.', $avatar_name);
            $extension = end($extension);
            if(in_array($extension, $allowed_files)) {
                if($avatar['size'] < 1000000) {
                    move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    $avatar_to_insert = $avatar_name;
                } else {
                    $_SESSION['edit-profile'] = "File size too big. Should be less than 1mb";
                }
            } else {
                $_SESSION['edit-profile'] = "File should be png, jpg, or jpeg";
            }
        }

        if(!isset($_SESSION['edit-profile'])) {
            // update user with only changed fields, excluding date_time
            $query = "UPDATE users SET 
                      firstname='$firstname', 
                      lastname='$lastname', 
                      avatar='$avatar_to_insert', 
                      bio='$bio' 
                      WHERE id=$id LIMIT 1";
            $result = mysqli_query($connection, $query);

            if(mysqli_errno($connection)) {
                $_SESSION['edit-profile'] = "Failed to update profile";
            } else {
                $_SESSION['edit-profile-success'] = "Profile updated successfully";
            }
        }
        
        header('location: ' . ROOT_URL . 'admin/profile.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/profile.php');
    die();
}
