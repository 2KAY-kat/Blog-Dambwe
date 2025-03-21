<?php
include ('config/constants.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $connection->prepare("SELECT email, token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);

    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rowsb > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $tokenExpiry = $row['token_expiry'];

        if (new DateTime() > new DateTime($tokenExpiry)) {
            echo "The rest token has expired. Please request a new password reset token.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $newPassword = $_POST['password'];
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateStmt = $connection->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");

                $updateStmt->bind_param("ss", $hashedPassword, $email);
                $updateStmt->execute();

                echo "Your password has been successfully rest!";
            }
        }
    } else {
        echo "Invalid token";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
</head>
<body>
    
<section class="form__section">
    <div class="container form__section-container">
        <h2>Reset Your Password</h2>
    
        <?php if (isset($email) && ! empty($email)): ?>
        <form action="reset-password.php?token=<?php echo urlencode($token); ?>" method="post">
        <input type="password" name="password" placeholder="Type your new password">
        <small style="border-left: solid 5px #011e31; padding: 5px;">OR <a href="signin.php">Sign In</a></small>


            <input class="btn" type="submit" name="submit" value="Reset Password">
            
        </form>

        <?php endif; ?>
    </div>
</section>
</body>
</html>