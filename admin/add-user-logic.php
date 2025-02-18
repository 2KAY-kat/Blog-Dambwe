<?php
require 'config/database.php';

// get the data on submit

if(isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_admin = filter_var($_POST['userrole'], FILTER_SANITIZE_NUMBER_INT);
    $avatar = $_FILES['avatar'];

    // validate values 

    if(!$firstname) {
        $_SESSION['add-user'] = "Please Enter First Name";
    } elseif (!$lastname) {
        $_SESSION['add-user'] = "Please Enter Last Name";
    } elseif (!$username) {
        $_SESSION['add-user'] = "Please Enter Username";
    } elseif (!$email) {
        $_SESSION['add-user'] = "Please Enter a valid Email";
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['add-user'] = "Password Should be more than 8 characters";
    } else {
        // matchbility of password

        if($createpassword !== $confirmpassword) {
            $_SESSION['signup'] = "Password do not match";
        } else {
            // hash password

            $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);

            // user or email existance in db

            $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
            $user_check_result = mysqli_query($connection, $user_check_query);
            if(mysqli_num_rows($user_check_result) > 0) {
                $_SESSION['add-user'] = "Username or Email already taken";
            } else {
                // process avatar/image 
                // If no avatar is uploaded, use default
                if(empty($avatar['name'])) {
                    $avatar_name = 'default-avatar.png';
                } else {
                    $time = time();
                    $avatar_name = $time . $avatar['name'];
                    $avatar_tmp_name = $avatar['tmp_name'];
                    $avatar_destination_path = '../images/' . $avatar_name;

                    // Make sure file is image
                    $allowed_files = ['png', 'jpg', 'jpeg'];
                    $extension = explode('.', $avatar_name);
                    $extension = end($extension);
                    if(in_array($extension, $allowed_files)) {
                        // Make sure image is not too large (2MB+)
                        if($avatar['size'] < 2000000) {
                            // Upload avatar
                            move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                        } else {
                            $_SESSION['add-user'] = "File size too big. Should be less than 2mb";
                        }
                    } else {
                        $_SESSION['add-user'] = "File should be png, jpg, or jpeg";
                    }
                }
            }
        }
    }

    // redirect to add-user page if any error

    if (isset($_SESSION['add-user'])) {
        // pass back to add user page the data 
        $_SESSION['add-user-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/add-user.php');
        die();
    } else {
        // add user to db
        
        $insert_user_query = "INSERT INTO users SET firstname='$firstname', lastname='$lastname', username='$username', email='$email', password='$hashed_password', avatar='$avatar_name', is_admin=$is_admin";
        $insert_user_result = mysqli_query($connection, $insert_user_query);

        if(!mysqli_errno($connection)) {
            // redirect to manage user page 
            $_SESSION['add-user-success'] = "New user $firstname $lastname added successfully";
            header('location: ' . ROOT_URL . 'admin/manage-users.php');
            die();
        }
    }

} else {
    // if was not submited go back 

    header('location: ' . ROOT_URL . 'admin/add-user.php');
    die();
}

header('location: ' . ROOT_URL . 'admin/manage-users.php');
die();