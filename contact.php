<?php
//include 'partials/header.php';


?>

<!--

    <section class="empty__page">
        <h1>Contact Page</h1>
    </section>

-->


   



<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $payment_method = $_POST['payment_method'];

    // Process payment or other logic here…

    echo "Checkout complete for " . $name;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    
<style>

*	{
    Box-sizing: border-box;
    Margin: 0;
    Padding: 0;
    Font-family: Arial, sans-serif;
}

body {
    Background-color: #f4f4f4;
    Display: flex;
    Justify-content: center;
    Align-items: center;
    Height: 100vh;
}

.checkout-container {
    Background-color: white;
    Border-radius: 8px;
    Padding: 20px;
    Box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    Width: 400px;
}

.checkout-form h2 {
    Margin: 20px 0 10px;
}

.input-group {
    Margin-bottom: 10px;
}

.input-group label {
    Display: block;
    Margin-bottom: 5px;
}

.input-group input[type="text"],
.input-group input[type="email"],
.input-group input[type="tel"],
.input-group input[type="radio"] {
    Width: 100%;
    Padding: 8px;
    Border: 1px solid #ccc;
    Border-radius: 4px;
}

.cart-item {
    Display: flex;
    Justify-content: space-between;
    Align-items: center;
    Margin-bottom: 20px;
}

.cart-item label {
    Font-size: 16px;
    Font-weight: bold;
}

.cart-item span {
    Font-size: 16px;
    Color: green;
}

.remove-cart {
    Font-size: 14px;
    Color: red;
    Text-decoration: none;
}

.payment-method input {
    Margin-right: 5px;
}

.terms {
    Display: flex;
    Align-items: center;
    Margin: 20px 0;
}

.terms input[type="checkbox"] {
    Margin-right: 10px;
}

.terms a {
    Color: blue;
    Text-decoration: none;
}

.checkout-btn {
    Width: 100%;
    Background-color: #007bff;
    Color: white;
    Border: none;
    Padding: 10px;
    Border-radius: 4px;
    Cursor: pointer;
}

.checkout-btn:hover {
    Background-color: #0056b3;
}

</style>

</head>
<body>
    <div class="checkout-container">
        <form action="checkout.php" method="POST" class="checkout-form">
            <!--Cart Item -->
            <div class="cart-item">
                <label for="subscription">One year</label>
                <span>US$9</span>
                <a href="#" class="remove-cart">Remove from cart</a>
            </div>

            <!--Contact Information -->
            <h2>Contact Information</h2>
            <div class="contact-info">
                <div class="input-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="Kelvin Katoya" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value=dambwedesigns@gmail.com required>
                </div>
                <div class="input-group">
                    <label for="phone">Phone</label>
                    <input type="tel" name="phone" id="phone" value="+265.897644624" required>
                </div>
                <div class="input-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" value="Chilobwe, Blantyre" required>
                </div>
                <div class="input-group">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city" value="Blantyre" required>
                </div>
                <div class="input-group">
                    <label for="country">Country</label>
                    <input type="text" name="country" id="country" value="Malawi" required>
                </div>
            </div>

            <!-- Payment Method -->
            <h2>Pick a payment method</h2>
            <div class="payment-method">
                <input type="radio" id="visa" name="payment_method" value="Visa" checked>
                <label for="visa">Visa</label>
                <input type="radio" id="mastercard" name="payment_method" value="Mastercard">
                <label for="mastercard">Mastercard</label>
                <input type="radio" id="amex" name="payment_method" value="Amex">
                <label for="amex">AMEX</label>
            </div>

            <div class="input-group">
                <label for="cardholder_name">Cardholder name</label>
                <input type="text" name="cardholder_name" id="cardholder_name" value="Kelvin Katoya" required>
            </div>

            <!--Terms and Submit Button -->
            <div class="terms">
                <input type="checkbox" name="agree" required>
                <label for="agree">By continuing, you agree to our <a href="#">Terms of Service</a>.</label>
            </div>
            
            <button type="submit" class="checkout-btn">Complete Checkout</button>
        </form>
    </div>
</body>
</html>


<?php
//include 'partials/footer.php';


?>