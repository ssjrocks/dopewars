<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // This is where you would handle sending an email with a password reset link.
    // For simplicity, we just update the user password to 'newpassword' (this is not secure, just for demonstration).
    
    $new_password = password_hash('newpassword', PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = :new_password WHERE username = :email");
    $stmt->bindParam(':new_password', $new_password);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    echo "A new password has been set. Please check your email.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        <form method="POST">
            Email: <input type="email" name="email" required>
            <button type="submit">Submit</button>
        </form>
        <button class="alt" onclick="window.location.href='login.php'">Back</button>
    </div>
</body>
</html>
