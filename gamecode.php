<?php
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

// Fetch goods
$goods_stmt = $conn->prepare("SELECT * FROM goods");
$goods_stmt->execute();
$goods = $goods_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations
$locations_stmt = $conn->prepare("SELECT * FROM locations");
$locations_stmt->execute();
$locations = $locations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current location ID from user data
$current_location_id = $user['location_id'];

// Fetch current location name
$current_location = '';
foreach ($locations as $location) {
    if ($location['id'] == $current_location_id) {
        $current_location = $location['name'];
        break;
    }
}

// Fetch inventory
$inventory_stmt = $conn->prepare("SELECT goods.id, goods.name, inventory.quantity, inventory.average_price FROM inventory JOIN goods ON inventory.good_id = goods.id WHERE inventory.user_id = :user_id");
$inventory_stmt->bindParam(':user_id', $user_id);
$inventory_stmt->execute();
$inventory = $inventory_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle errors
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_buy':
            $error_message = "You wanna buy nothing?";
            break;
        case 'insufficient_cash':
            $error_message = "You can't afford that.";
            break;
        case 'invalid_sell':
            $error_message = "Sell what, your soul?";
            break;
        case 'insufficient_inventory':
            $error_message = "Hey you tryna stiff me?";
            break;
    }
}
?>