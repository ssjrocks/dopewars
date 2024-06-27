<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT cash, bank, debt, health FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$response = ['status' => 'success'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['repay_amount'])) {
        $repay_amount = (float) $_POST['repay_amount'];
        if ($repay_amount > 0 && $repay_amount <= $user['cash'] && $repay_amount <= $user['debt']) {
            $stmt = $conn->prepare("UPDATE users SET cash = cash - :repay_amount, debt = debt - :repay_amount WHERE id = :id");
            $stmt->bindParam(':repay_amount', $repay_amount);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } else {
            $response = ['status' => 'error', 'message' => 'Insufficient funds'];
        }
    }

    if (isset($_POST['cash_advance'])) {
        $advance_amount = (float) $_POST['cash_advance'];
        if ($advance_amount > 0) {
            $stmt = $conn->prepare("UPDATE users SET cash = cash + :advance_amount, debt = debt + :advance_amount WHERE id = :id");
            $stmt->bindParam(':advance_amount', $advance_amount);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } else {
            $response = ['status' => 'error', 'message' => 'Insufficient funds'];
        }
    }

    if (isset($_POST['deposit_amount'])) {
        $deposit_amount = (float) $_POST['deposit_amount'];
        if ($deposit_amount > 0 && $deposit_amount <= $user['cash']) {
            $stmt = $conn->prepare("UPDATE users SET cash = cash - :deposit_amount, bank = bank + :deposit_amount WHERE id = :id");
            $stmt->bindParam(':deposit_amount', $deposit_amount);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } else {
            $response = ['status' => 'error', 'message' => 'Insufficient funds'];
        }
    }

    if (isset($_POST['withdraw_amount'])) {
        $withdraw_amount = (float) $_POST['withdraw_amount'];
        if ($withdraw_amount > 0 && $withdraw_amount <= $user['bank']) {
            $stmt = $conn->prepare("UPDATE users SET cash = cash + :withdraw_amount, bank = bank - :withdraw_amount WHERE id = :id");
            $stmt->bindParam(':withdraw_amount', $withdraw_amount);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } else {
            $response = ['status' => 'error', 'message' => 'Insufficient funds'];
        }
    }

    // Refresh the user data after updates
    $stmt = $conn->prepare("SELECT cash, bank, debt, health FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['user'] = $user;
}

echo json_encode($response);
?>
