<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Reset user state
$stmt = $conn->prepare("UPDATE users SET cash = 2000, bank = 0, debt = 5500, health = 100, location_id = 1 WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();

// Clear inventory
$stmt = $conn->prepare("DELETE FROM inventory WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

header('Location: game.php');
?>
