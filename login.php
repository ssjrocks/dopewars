<?php
include 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: game.php');
    } else {
        echo "Invalid credentials";
    }
}

// Fetch total users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST">
            Email: <input type="email" name="email" required>
            Password: <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <button onclick="window.location.href='register.php'">Register</button>
        <button onclick="window.location.href='forgot_password.php'">Forgot Password</button>
        <p>Total Users: <?php echo $total_users; ?></p>
    </div>
</body>
</html>
