<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$good_id = $_POST['good_id'];

// Fetch good price
$stmt = $conn->prepare("SELECT * FROM goods WHERE id = :id");
$stmt->bindParam(':id', $good_id);
$stmt->execute();
$good = $stmt->fetch(PDO::FETCH_ASSOC);
$price = rand($good['min_price'], $good['max_price']);

// Update user cash
$stmt = $conn->prepare("UPDATE users SET cash = cash - :price WHERE id = :id");
$stmt->bindParam(':price', $price);
$stmt->bindParam(':id', $user_id);
$stmt->execute();

// Update user inventory
$stmt = $conn->prepare("INSERT INTO inventory (user_id, good_id, quantity) VALUES (:user_id, :good_id, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':good_id', $good_id);
$stmt->execute();

header('Location: game.php');
?>

// sell.php
<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$good_id = $_POST['good_id'];

// Fetch good price
$stmt = $conn->prepare("SELECT * FROM goods WHERE id = :id");
$stmt->bindParam(':id', $good_id);
$stmt->execute();
$good = $stmt->fetch(PDO::FETCH_ASSOC);
$price = rand($good['min_price'], $good['max_price']);

// Update user cash
$stmt = $conn->prepare("UPDATE users SET cash = cash + :price WHERE id = :id");
$stmt->bindParam(':price', $price);
$stmt->bindParam(':id', $user_id);
$stmt->execute();

// Update user inventory
$stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE user_id = :user_id AND good_id = :good_id AND quantity > 0");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':good_id', $good_id);
$stmt->execute();

header('Location: game.php');
?>
