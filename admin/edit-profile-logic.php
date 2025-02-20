<?php
require '../config/database.php';

if(isset($_POST['submit'])) {
    $id = $_SESSION['user-id'];
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate inputs
    if(!$firstname || !$lastname) {
        $_SESSION['edit-profile'] = "Please enter both first and last name";
    } else {
        // Get current user data for existing avatar/cover
        $current_user_query = "SELECT avatar, cover_photo FROM users WHERE id=$id";
        $current_user_result = mysqli_query($connection, $current_user_query);
        $current_user = mysqli_fetch_assoc($current_user_result);

        $avatar_name = $current_user['avatar'];
        $cover_photo_name = $current_user['cover_photo'];

        // Handle new avatar upload
        if($_FILES['avatar']['name']) {
            $avatar = $_FILES['avatar'];
            $time = time();
            $avatar_name = $time . $avatar['name'];
            $avatar_tmp = $avatar['tmp_name'];
            $avatar_path = '../images/' . $avatar_name;

            // Validate avatar
            $allowed = ['png', 'jpg', 'jpeg'];
            $ext = explode('.', $avatar_name);
            $ext = end($ext);
            if(in_array(strtolower($ext), $allowed)) {
                if($avatar['size'] < 2000000) {
                    move_uploaded_file($avatar_tmp, $avatar_path);
                } else {
                    $_SESSION['edit-profile'] = "Avatar file too large. Maximum size is 2MB";
                }
            } else {
                $_SESSION['edit-profile'] = "Avatar must be PNG, JPG, or JPEG";
            }
        }

        // Handle new cover photo upload
        if($_FILES['cover_photo']['name']) {
            $cover = $_FILES['cover_photo'];
            $time = time();
            $cover_photo_name = $time . $cover['name'];
            $cover_tmp = $cover['tmp_name'];
            $cover_photo_path = '../images/' . $cover_photo_name;

            // Validate cover photo
            $allowed = ['png', 'jpg', 'jpeg'];
            $ext = explode('.', $cover_photo_name);
            $ext = end($ext);
            if(in_array(strtolower($ext), $allowed)) {
                if($cover['size'] < 2000000) {
                    move_uploaded_file($cover_tmp, $cover_photo_path);
                } else {
                    $_SESSION['edit-profile'] = "Cover photo file too large. Maximum size is 2MB";
                }
            } else {
                $_SESSION['edit-profile'] = "Cover photo must be PNG, JPG, or JPEG";
            }
        }

        if(!isset($_SESSION['edit-profile'])) {
            // Update user in database
            $update_query = "UPDATE users SET firstname=?, lastname=?, bio=?, avatar=?, cover_photo=? WHERE id=?";
            $stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($stmt, "sssssi", $firstname, $lastname, $bio, $avatar_name, $cover_photo_name, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['edit-profile-success'] = "Profile updated successfully";
                
                // Update session data
                $_SESSION['user-data'] = [
                    'id' => $id,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'avatar' => $avatar_name,
                    'cover' => $cover_photo_name
                ];
            } else {
                $_SESSION['edit-profile'] = "Error updating profile";
            }
        }
    }
}

header('location: ' . ROOT_URL . 'admin/profile.php');
die();
