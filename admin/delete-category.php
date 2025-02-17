<?php
require 'config/database.php';

if(isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // upadate  category_id of posts that has been deleted to remain in the db once a certain category has been deleted and it contained data (posts) to avoid data loss and errorrs. So this is then sent to the uncategorised_id......
    //thats right am a genius !!! LOL.
    #now 

    $update_query = "UPDATE posts SET category_id=7 WHERE category_id=$id"; 
    $update_result = mysqli_query($connection, $update_query);

    if (!mysqli_errno($connection)) {
            // delete category
    $query = "DELETE FROM categories WHERE id=$id LIMIT 1";
    $result = mysqli_query($connection, $query);
    $_SESSION['delete-category-success'] = "Category deleted successfully";
    }



}

header('location: ' . ROOT_URL . 'admin/manage-categories.php');
die();