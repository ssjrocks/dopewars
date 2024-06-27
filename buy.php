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
    echo json_encode(['status' => 'error', 'message' => 'You wanna buy nothing?']);
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

        // Check if the item already exists in the inventory
        $inventory_check_stmt = $conn->prepare("SELECT quantity, average_price FROM inventory WHERE user_id = :user_id AND good_id = :good_id");
        $inventory_check_stmt->bindParam(':user_id', $user_id);
        $inventory_check_stmt->bindParam(':good_id', $good_id);
        $inventory_check_stmt->execute();
        $inventory_item = $inventory_check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($inventory_item) {
            // Calculate the new average price
            $new_quantity = $inventory_item['quantity'] + $quantity;
            $new_average_price = (($inventory_item['quantity'] * $inventory_item['average_price']) + ($quantity * $price)) / $new_quantity;

            // Update inventory with new quantity and average price
            $update_inventory_stmt = $conn->prepare("UPDATE inventory SET quantity = :quantity, average_price = :average_price WHERE user_id = :user_id AND good_id = :good_id");
            $update_inventory_stmt->bindParam(':quantity', $new_quantity);
            $update_inventory_stmt->bindParam(':average_price', $new_average_price);
            $update_inventory_stmt->bindParam(':user_id', $user_id);
            $update_inventory_stmt->bindParam(':good_id', $good_id);
            $update_inventory_stmt->execute();
        } else {
            // Insert new item into inventory
            $insert_inventory_stmt = $conn->prepare("INSERT INTO inventory (user_id, good_id, quantity, average_price) VALUES (:user_id, :good_id, :quantity, :average_price)");
            $insert_inventory_stmt->bindParam(':user_id', $user_id);
            $insert_inventory_stmt->bindParam(':good_id', $good_id);
            $insert_inventory_stmt->bindParam(':quantity', $quantity);
            $insert_inventory_stmt->bindParam(':average_price', $price);
            $insert_inventory_stmt->execute();
        }

        // Fetch updated user data
        $stmt = $conn->prepare("SELECT cash, bank, debt FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch updated inventory
        $inventory_stmt = $conn->prepare("SELECT goods.id, goods.name, inventory.quantity, inventory.average_price FROM inventory JOIN goods ON inventory.good_id = goods.id WHERE inventory.user_id = :user_id");
        $inventory_stmt->bindParam(':user_id', $user_id);
        $inventory_stmt->execute();
        $inventory = $inventory_stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'user' => $user, 'inventory' => $inventory]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'You can\'t afford that.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid good selected.']);
}
?>