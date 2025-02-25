<?php
require '../config/database.php';

if (!isset($_SESSION['user-id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];
    $time = time();
    $avatar_name = $time . $avatar['name'];
    $avatar_tmp_name = $avatar['tmp_name'];
    $avatar_destination_path = '../images/' . $avatar_name;

    // Make sure file is an image
    $allowed_files = ['png', 'jpg', 'jpeg', 'webp'];
    $extension = explode('.', $avatar_name)[1];
    
    if (in_array($extension, $allowed_files)) {
        // Make sure image is not too large (1MB+)
        if ($avatar['size'] < 1000000) {
            // Upload avatar
            move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File size too big. Should be less than 1MB']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File should be png, jpg, jpeg, or webp']);
        exit;
    }

    // Delete old avatar if it exists and is not the default avatar
    $user_id = $_SESSION['user-id'];
    $query = "SELECT avatar FROM users WHERE id=?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $old_avatar_name = mysqli_fetch_assoc($result)['avatar'];

    if ($old_avatar_name && $old_avatar_name !== 'default-avatar.png') {
        $old_avatar_path = '../images/' . $old_avatar_name;
        if (file_exists($old_avatar_path)) {
            unlink($old_avatar_path);
        }
    }

    // Update avatar in database
    $query = "UPDATE users SET avatar=? WHERE id=?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "si", $avatar_name, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success',
            'avatar_url' => ROOT_URL . 'images/' . $avatar_name,
            'message' => 'Avatar updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database update failed'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No file uploaded'
    ]);
}
