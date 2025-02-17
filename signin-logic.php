<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    //fetch data

    $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if(!$username_email) {
        $_SESSION['signin'] = "Username Or Email Required";
    } elseif (!$password) {
        $_SESSION['signin'] = "Password Required";
    } else {
        //fetch user from db
        $fetch_user_query = "SELECT * FROM users WHERE username='$username_email' OR email='$username_email'";
        $fetch_user_result = mysqli_query($connection, $fetch_user_query);

        if(mysqli_num_rows($fetch_user_result) == 1) {
            //convert record into assoc array 

            $user_record = mysqli_fetch_assoc($fetch_user_result);
            $db_password = $user_record['password'];
            // compare form pass to db pass

            if(password_verify($password, $db_password)) {
                // set session for accessibility

                $_SESSION['user-id'] = $user_record['id'];

                // if the admin or not 
                if($user_record['is_admin'] == 1) {
                    $_SESSION['user_is_admin'] = true;
                }

                //log user in
                header('location: ' . ROOT_URL . './');

            }  else {
                $_SESSION['signin'] = "Please Make Sure you use the Correct Input";
            }
        } else {
            $_SESSION['signin'] = "User Not Found";
        }
    }

    // if any errors go back to signin

    if(isset($_SESSION['signin'])) {
        $_SESSION['signin_data'] = $_POST;
        header('location: ' . ROOT_URL . 'signin.php');
        die();
    }


}else {
    header('location: ' . ROOT_URL . 'signin.php');
    die();
}