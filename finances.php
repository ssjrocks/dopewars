<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Finances</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Finances</h1>
        <p>Cash: $<?php echo $user['cash']; ?></p>
        <p>Bank: $0</p>
        <p>Debt: $5,500</p>
        <p>Health: 100%</p>
        <button onclick="window.location.href='game.php'">Back to Game</button>
    </div>
</body>
</html>
