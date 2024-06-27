<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$good_id = $_POST['good_id'];
$quantity = $_POST['quantity'];

if (empty($good_id) || empty($quantity)) {
    echo json_encode(['status' => 'error', 'message' => 'Sell what, your soul?']);
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

        // Fetch updated user data
        $stmt = $conn->prepare("SELECT cash, bank, debt FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch updated inventory
        $inventory_stmt = $conn->prepare("SELECT goods.name, inventory.quantity FROM inventory JOIN goods ON inventory.good_id = goods.id WHERE user_id = :user_id");
        $inventory_stmt->bindParam(':user_id', $user_id);
        $inventory_stmt->execute();
        $inventory = $inventory_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'user' => $user, 'inventory' => $inventory]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hey you tryna stiff me?']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid good selected.']);
}
?>
