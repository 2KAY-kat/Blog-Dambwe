<?php
require '../config/database.php';

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user-id'];
    
    // Sanitize form inputs
    $display_name = filter_var($_POST['display_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $bio = filter_var($_POST['bio'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $theme_color = filter_var($_POST['theme_color'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $privacy_setting = filter_var($_POST['privacy_setting'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Handle social links
    $social_links = array_filter($_POST['social_links']); // Remove empty values
    $social_links_json = json_encode($social_links);

    // Handle file uploads
    $avatar = $_FILES['avatar'];
    $cover_photo = $_FILES['cover_photo'];
    
    $avatar_name = null;
    $cover_name = null;

    if ($avatar['name']) {
        // Process avatar upload
        $time = time();
        $avatar_name = $time . $avatar['name'];
        $avatar_tmp = $avatar['tmp_name'];
        $avatar_path = '../images/' . $avatar_name;

        // Validate avatar
        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), $allowed)) {
            $_SESSION['edit-profile'] = "Avatar must be PNG, JPG, or JPEG";
        } elseif ($avatar['size'] > 2000000) {
            $_SESSION['edit-profile'] = "Avatar file too large. Maximum size is 2MB";
        } else {
            move_uploaded_file($avatar_tmp, $avatar_path);
        }
    }

    if ($cover_photo['name']) {
        // Process cover photo upload
        $time = time();
        $cover_name = $time . $cover_photo['name'];
        $cover_tmp = $cover_photo['tmp_name'];
        $cover_path = '../images/' . $cover_name;

        // Validate cover photo
        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = pathinfo($cover_photo['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($ext), $allowed)) {
            $_SESSION['edit-profile'] = "Cover photo must be PNG, JPG, or JPEG";
        } elseif ($cover_photo['size'] > 2000000) {
            $_SESSION['edit-profile'] = "Cover photo file too large. Maximum size is 2MB";
        } else {
            move_uploaded_file($cover_tmp, $cover_path);
        }
    }

    if (!isset($_SESSION['edit-profile'])) {
        // Start transaction
        mysqli_begin_transaction($connection);
        
        try {
            // Update users table
            $update_user = "UPDATE users SET 
                          username = ?, 
                          display_name = ?, 
                          bio = ?";
            $params = [$username, $display_name, $bio];
            $types = "sss";
            
            if ($avatar_name) {
                $update_user .= ", avatar = ?";
                $params[] = $avatar_name;
                $types .= "s";
            }
            
            if ($cover_name) {
                $update_user .= ", cover_photo = ?";
                $params[] = $cover_name;
                $types .= "s";
            }
            
            $update_user .= " WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = mysqli_prepare($connection, $update_user);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);

            // Update user_profiles table
            $update_profile = "INSERT INTO user_profiles (user_id, theme_color, privacy_setting, social_links) 
                             VALUES (?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE 
                             theme_color = VALUES(theme_color),
                             privacy_setting = VALUES(privacy_setting),
                             social_links = VALUES(social_links)";
            
            $stmt = mysqli_prepare($connection, $update_profile);
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $theme_color, $privacy_setting, $social_links_json);
            mysqli_stmt_execute($stmt);

            // Commit transaction
            mysqli_commit($connection);
            
            $_SESSION['edit-profile-success'] = "Profile updated successfully";
            
            // Update session data
            $_SESSION['user-data'] = [
                'username' => $username,
                'display_name' => $display_name,
                'avatar' => $avatar_name ?: $_SESSION['user-data']['avatar'],
                'cover_photo' => $cover_name ?: $_SESSION['user-data']['cover_photo']
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($connection);
            $_SESSION['edit-profile'] = "Error updating profile: " . $e->getMessage();
        }
    }
}

header('location: ' . ROOT_URL . 'admin/profile.php');
die();
