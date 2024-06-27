<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$new_location_id = $_POST['location_id'];

// Update user location
$stmt = $conn->prepare("UPDATE users SET location_id = :location_id WHERE id = :id");
$stmt->bindParam(':location_id', $new_location_id);
$stmt->bindParam(':id', $user_id);
$stmt->execute();

header('Location: game.php');
?>
