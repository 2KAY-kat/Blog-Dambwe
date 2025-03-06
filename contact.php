<?php
include 'partials/header.php';
require_once 'config/email_config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validate input
    $errors = [];
    if (!$name) $errors[] = "Name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (!$subject) $errors[] = "Subject is required";
    if (!$message) $errors[] = "Message is required";

    if (empty($errors)) {
        $to = ADMIN_EMAIL;
        $email_subject = EMAIL_SUBJECT_PREFIX . $subject;
        
        // Convert headers array to string
        $headers = 'From: ' . $email . "\r\n" .
                  'Reply-To: ' . $email . "\r\n" .
                  'X-Mailer: PHP/' . phpversion() . "\r\n" .
                  'Content-Type: text/plain; charset=UTF-8';
        
        // Prepare email body
        $email_body = "You have received a new message from your website contact form.\n\n";
        $email_body .= "Name: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Subject: $subject\n\n";
        $email_body .= "Message:\n$message";

        // Send email
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success_message = "Thank you for your message, $name! We'll get back to you soon.";
        } else {
            $errors[] = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
}
?>

<section class="contact-section">
    <div class="container contact-container">
        <h2>Get In Touch</h2>
        <div class="contact-wrapper">
            <!-- Contact Information -->
            <div class="contact-info">
                <h3>Contact Information</h3>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>Chilobwe, Blantyre<br>Malawi</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <p>dambwedesigns@gmail.com</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <p>+265 897644624</p>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <?php if (isset($success_message)): ?>
                    <div class="alert success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert error">
                        <?php foreach($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>

:root {
    --color-primary:  #243346;
    --color-primary-light: #214b77;
    --color-primar-variant: #0056b3;
    --color-gray-900: #011e31;
    --color-gray-700: #767e86;
    --color-gray-300: rgba(242, 242, 254, 0.3);
    --color-gray-200: rgba(242, 242, 254, 0.7);
    --color-green: #106935;
    --color-green-light: hsl(145, 74%, 24%, 15%);
    --color-white: #fff;
    --color-bg: #2e3a46;
    --red-color: #7c1010;
    --red-color-light: hsl(0, 76%, 8%);

    --transition: all 300ms ease;


    --container-width-lg: 74%;
    --container-width-md: 88%;
    --form-width-: 40%;

    --card-boder-radius-1: 0.1rem;
    --card-boder-radius-2: 0.2rem;
    --card-boder-radius-3: 0.4rem;
    --card-boder-radius-4: 1rem;
    --card-boder-radius-5: 3rem;
  }
.contact-section {
    padding: 5rem 0;
    background-color: var(--color-bg);
}

.contact-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.contact-container h2 {
    text-align: center;
    margin-bottom: 3rem;
    color: var(--color-white);
}

.contact-wrapper {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 4rem;
}

.contact-info {
    background: var(--color-primary);
    color: var(--color-white);
    padding: 2rem;
    border-radius: 10px;
}

.contact-info h3 {
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.info-item i {
    margin-right: 1rem;
    font-size: 1.2rem;
}

.social-links {
    margin-top: 2rem;
}

social-links a {
    color: var(--color-white);
    margin-right: 1rem;
    font-size: 1.5rem;
    transition: color 0.3s;
}

social-links a:hover {
    color: var(--color-primary-light);
}

.contact-form {
    background: var(--color-bg);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--color-dark);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 5px;
}

.alert.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .contact-wrapper {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'partials/footer.php'; ?>