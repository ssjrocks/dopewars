<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$good_id = $_POST['good_id'];
$quantity = $_POST['quantity'];

if (empty($good_id) || empty($quantity)) {
    header('Location: game.php?error=invalid_buy');
    exit();
}

// Fetch good price
$stmt = $conn->prepare("SELECT * FROM goods WHERE id = :id");
$stmt->bindParam(':id', $good_id);
$stmt->execute();
$good = $stmt->fetch(PDO::FETCH_ASSOC);

if ($good) {
    $price = rand($good['min_price'], $good['max_price']);
    $total_cost = $price * $quantity;

    // Check if user has enough cash
    $stmt = $conn->prepare("SELECT cash FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['cash'] >= $total_cost) {
        // Update user cash
        $stmt = $conn->prepare("UPDATE users SET cash = cash - :total_cost WHERE id = :id");
        $stmt->bindParam(':total_cost', $total_cost);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        // Update user inventory
        $stmt = $conn->prepare("INSERT INTO inventory (user_id, good_id, quantity) VALUES (:user_id, :good_id, :quantity)
                                ON DUPLICATE KEY UPDATE quantity = quantity + :quantity");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':good_id', $good_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();

        header('Location: game.php');
    } else {
        header('Location: game.php?error=insufficient_cash');
    }
} else {
    header('Location: game.php?error=invalid_buy');
}
?>
