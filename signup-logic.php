<?php 
session_start();
require 'config/database.php';

// get the data on submit

if(isset($_POST['submit'])) {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword = filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword = filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    // validate values 

    if(!$firstname) {
        $_SESSION['signup'] = "Please Enter Your First Name";
    } elseif (!$lastname) {
        $_SESSION['signup'] = "Please Enter Your Last Name";
    } elseif (!$username) {
        $_SESSION['signup'] = "Please Enter Your Username";
    } elseif (!$email) {
        $_SESSION['signup'] = "Please Enter Your a valid Email";
    } elseif (strlen($createpassword) < 8 || strlen($confirmpassword) < 8) {
        $_SESSION['signup'] = "Password Should be more than 8 characters";
    } elseif (!$avatar['name']) {
        $_SESSION['signup'] = "Please Select an Image";
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
                $_SESSION['signup'] = "Username or Email already taken";
            } else {
                // process avatar/image 
                // rename the avatar

                $time = time(); // make every avatar unique using time stamps

                $avatar_name = $time . $avatar['name'];
                $avatar_tmp_name = $avatar['tmp_name'];
                $avatar_destination_path = 'images/' . $avatar_name;

                // make sure file is an image 

                $allowed_files = ['png', 'jpg', 'jpeg'];
                $extention = explode('.', $avatar_name);
                $extention = end($extention);
                if(in_array($extention, $allowed_files)) {
                    // maintainin a small sized image (imb+)
                    if($avatar['size'] < 1000000) {
                        //upload norma size 
                        move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    } else {
                        $_SESSION['signup'] = 'The Image is too large should be less than 1mb';
                    }
                } else {
                    $_SESSION['signup'] = "File Should be png, jpg or jpeg";
                }

            }

        }
    }

    // redirect to signup page if any error

    if (isset($_SESSION['signup'])) {
        // pass back to signup page the data 
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    } else {
        // add user to db
        
        $current_time = date('Y-m-d H:i:s');
        $query = "INSERT INTO users (firstname, lastname, username, email, password, avatar, is_admin, date_time) 
                  VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "sssssss", $firstname, $lastname, $username, $email, $hashed_password, $avatar_name, $current_time);
        mysqli_stmt_execute($stmt);

        if(!mysqli_errno($connection)) {
            // redirect to login page 
            $_SESSION['signup-success'] = "Registered Successfully, please Login";
            header('location: ' . ROOT_URL . 'signin.php');
            die();
        }
    }

} else {
    // if was not submited go back 

    header('location: ' . ROOT_URL . 'signup.php');
    die();
}