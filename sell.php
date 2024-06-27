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
    header('Location: game.php?error=invalid_sell');
    exit();
}

// Fetch good price
$stmt = $conn->prepare("SELECT * FROM goods WHERE id = :id");
$stmt->bindParam(':id', $good_id);
$stmt->execute();
$good = $stmt->fetch(PDO::FETCH_ASSOC);

if ($good) {
    $price = rand($good['min_price'], $good['max_price']);
    $total_revenue = $price * $quantity;

    // Check if user has enough quantity to sell
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE user_id = :user_id AND good_id = :good_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':good_id', $good_id);
    $stmt->execute();
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inventory && $inventory['quantity'] >= $quantity) {
        // Update user cash
        $stmt = $conn->prepare("UPDATE users SET cash = cash + :total_revenue WHERE id = :id");
        $stmt->bindParam(':total_revenue', $total_revenue);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        // Update user inventory
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - :quantity WHERE user_id = :user_id AND good_id = :good_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':good_id', $good_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();

        header('Location: game.php');
    } else {
        header('Location: game.php?error=insufficient_inventory');
    }
} else {
    header('Location: game.php?error=invalid_sell');
}
?>
