<?php 
session_start();
require 'config/database.php';

// Step 1 processing
if (isset($_POST['next1'])) {
    // Get form data
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate input values
    if (!$firstname || !$lastname) {
        $_SESSION['signup'] = "Please enter your First and Last Name";
    } elseif (!$username) {
        $_SESSION['signup'] = "Please enter your Username";
    } else {
        // Store data in session
        $_SESSION['signup-data']['firstname'] = $firstname;
        $_SESSION['signup-data']['lastname'] = $lastname;
        $_SESSION['signup-data']['username'] = $username;

        // Advance to next step
        $_SESSION['signup-step'] = 2;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    }

    // Redirect back to signup page if there was a problem
    $_SESSION['signup-data'] = $_POST;
    header('location: ' . ROOT_URL . 'signup.php');
    die();
}

// Step 2 processing
if (isset($_POST['next2'])) {
    // Get form data
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate input values
    if (!$email) {
        $_SESSION['signup'] = "Please enter a valid email";
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['signup'] = "Password should be 8+ characters";
    } elseif ($createpassword !== $confirmpassword) {
        $_SESSION['signup'] = "Passwords do not match";
    } else {
        // Store data in session
        $_SESSION['signup-data']['email'] = $email;
        $_SESSION['signup-data']['createpassword'] = $createpassword;
        $_SESSION['signup-data']['confirmpassword'] = $confirmpassword;

        // Advance to next step
        $_SESSION['signup-step'] = 3;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    }

    // Redirect back to signup page if there was a problem
    $_SESSION['signup-data'] = $_POST;
    header('location: ' . ROOT_URL . 'signup.php');
    die();
}

// Final submission
if (isset($_POST['submit'])) {
    // Get form data from session
    $firstname = $_SESSION['signup-data']['firstname'];
    $lastname = $_SESSION['signup-data']['lastname'];
    $username = $_SESSION['signup-data']['username'];
    $email = $_SESSION['signup-data']['email'];
    $createpassword = $_SESSION['signup-data']['createpassword'];

    $avatar = $_FILES['avatar'];

    // Hash password
    $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

    // Check if username or email already exist in database
    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $user_check_result = mysqli_query($connection, $user_check_query);
    if (mysqli_num_rows($user_check_result) > 0) {
        $_SESSION['signup'] = "Username or Email already exists";
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    }

    // Work on avatar
    // If no avatar is uploaded, use default
    if (empty($avatar['name'])) {
        $avatar_name = 'default-avatar.png';
    } else {
        $time = time();
        $avatar_name = $time . $avatar['name'];
        $avatar_tmp_name = $avatar['tmp_name'];
        $avatar_destination_path = 'images/' . $avatar_name;

        // Validate file
        $allowed_files = ['png', 'jpg', 'jpeg'];
        $extension = explode('.', $avatar_name);
        $extension = end($extension);
        if (in_array($extension, $allowed_files)) {
            // Make sure file is not too large (2mb)
            if ($avatar['size'] < 2000000) {
                // Upload avatar
                move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
            } else {
                $_SESSION['signup'] = 'File size too big. Should be less than 2mb';
            }
        } else {
            $_SESSION['signup'] = 'File should be png, jpg, or jpeg';
        }
    }

    // Insert new user into database
    $insert_user_query = "INSERT INTO users (firstname, lastname, username, email, password, avatar, is_admin, date_time) VALUES ('$firstname', '$lastname', '$username', '$email', '$hashed_password', '$avatar_name', 0, NOW())";
    $insert_user_result = mysqli_query($connection, $insert_user_query);

    if (!mysqli_errno($connection)) {
        // Redirect to login page with success message
        $_SESSION['signup-success'] = "Registration successful. Please log in.";
        unset($_SESSION['signup-data']);
        unset($_SESSION['signup-step']);
        header('location: ' . ROOT_URL . 'signin.php');
        die();
    }
}

// If anything goes wrong, redirect to the first step
$_SESSION['signup-step'] = 1;
header('location: ' . ROOT_URL . 'signup.php');
die();