<?php
require 'config/database.php';

if(isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // validate input 
    if(!$title || !$description) {
        $_SESSION['edit-category'] = "Invalid inputs on the Edits";
    } else {
        // update

        $query = "UPDATE categories SET title='$title', description='$description' WHERE id=$id LIMIT 1";
        $result = mysqli_query($connection, $query);

        if(mysqli_errno($connection)) {
            $_SESSION['edit-category'] = "Failed Upadating Category";
        } else {
            $_SESSION['edit-category-success'] = "Category $title Updated Successfully";
        }
    }
}

header('location: ' . ROOT_URL . 'admin/manage-categories.php');
die();