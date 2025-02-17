<?php 
require 'config/database.php';

if(isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // for some reason we have to delete kaye the pic from db when deleting the post so we go into images folder and do the magic!!!!!

    $query = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($connection, $query);

    //get one leave the rest freee its pizza thingy LOL.

    if (mysqli_num_rows($result) == 1) {
        $post = mysqli_fetch_assoc($result);
        $thumbnail_name = $post['thumbnail'];
        $thumbnail_path = '../images/' . $thumbnail_name;


        // delete
        if ($thumbnail_path) {
            unlink($thumbnail_path);

            $delete_post_query = "DELETE FROM posts WHERE id=$id LIMIT 1";
            $delete_post_result = mysqli_query($connection, $delete_post_query);

            if(!mysqli_errno($connection)) {
                $_SESSION['delete-post-success'] = "Post deleted Successfully";
            }

        }
    }
}

header('location: ' . ROOT_URL . 'admin/');
die();