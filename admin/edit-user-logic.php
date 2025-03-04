<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    // get updated data

    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_admin = filter_var($_POST['userrole'], FILTER_SANITIZE_NUMBER_INT);

    // check valid input

    if (!$firstname || !$lastname) {
        $_SESSION['edit-user'] = "User $firstname $lastname was unable to be edited";
    } else {
        // update user

        $query = "UPDATE users SET firstname='$firstname', lastname='$lastname', is_admin=$is_admin WHERE id=$id LIMIT 1";
        $result = mysqli_query($connection, $query);

        if (mysqli_errno($connection)) {
            $_SESSION['edit-user'] = "Failed to Update User";
        } else {
            $_SESSION['edit-user-success'] = "User $firstname $lastname updated Successfully";
        }
    }


    header('location: ' . ROOT_URL . 'admin/manage-users.php');
die();

}