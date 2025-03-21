<?php
include ('config/database.php');

if (!$connection) {
    die("Connection failed: " . mysqli_error($connection));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $connection->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    $stmt->execute();
    $result = $stmt->get_result();

    //look for email in db and process it if exists

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));

        $tokenExpiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $updateStmt = $connection->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        if (!$updateStmt) {
            die("Error in SQL query: " . $connection->error);
        }

        $updateStmt->bind_param("sss", $token, $tokenExpiry, $email);
        $updateStmt->execute();

        $resetLink = "http://localhost/Blog-dambwe/reset-password.php?token=" . urlencode($token);

        $subject = "Password Reset Request";
        $message = "Click the link to reset your password:\n\n" . $resetLink;
        $headers = "From: no-reply@dambwedesigns@gmail.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "Check your email for a reset link";
        } else {
            echo "failed to send email";
        }
    } else {
        echo "Email not found";
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
        <h2>Forgot Password</h2>
    
        <form action="forget-password.php" method="post">
        <input type="email" name="email" placeholder="Enter your email to get a password reset link" required>
        <small style="border-left: solid 5px #011e31; padding: 5px;">OR <a href="signin.php">Sign In</a></small>
            <button class="btn" type="submit" name="submit">Send Reset Link</button>
            
        </form>
    </div>
</section>
</body>
</html>