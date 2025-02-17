<?php
include 'partials/header.php';

if(isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM users WHERE id=$id";
    $result = mysqli_query($connection, $query);
    $user = mysqli_fetch_assoc($result);
} else  {
    header('location: ' . ROOT_URL . 'admin/manage-users.php');
    die();
}

?>


<section class="form__section">
    <div class="container form__section-container">
        <h2>Edit User</h2>
       
        <form action="<?= ROOT_URL ?>admin/edit-user-logic.php" method="post">
            <input type="hidden" value="<?= $user['id'] ?>" name="id">
            <input type="text" value="<?= $user['firstname'] ?>" name="firstname" placeholder="First Name">
            <input type="text" value="<?= $user['lastname'] ?>" name="lastname" placeholder="Last Name">
            <select name="userrole">
                <option value="0">Author</option>
                <option value="1">Admin</option>
            </select>

            <?php if($_SESSION['user_is_admin']): ?>
                <div class="form__control inline">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1" <?= $user['is_admin'] ? 'checked' : '' ?>>
                    <label for="is_admin">Make Admin</label>
                </div>
            <?php endif ?>

            <button class="btn" name="submit" type="submit">Update User</button>
        </form>
    </div>
</section>

