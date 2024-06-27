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

// Handle repayments and deposits
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['repay_amount'])) {
        $repay_amount = $_POST['repay_amount'];
        $stmt = $conn->prepare("UPDATE users SET cash = cash - :repay_amount, debt = debt - :repay_amount WHERE id = :id");
        $stmt->bindParam(':repay_amount', $repay_amount);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    if (isset($_POST['cash_advance'])) {
        $advance_amount = $_POST['cash_advance'];
        $stmt = $conn->prepare("UPDATE users SET cash = cash + :advance_amount, debt = debt + :advance_amount WHERE id = :id");
        $stmt->bindParam(':advance_amount', $advance_amount);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }

    if (isset($_POST['deposit_amount'])) {
        $deposit_amount = $_POST['deposit_amount'];
        $stmt = $conn->prepare("UPDATE users SET cash = cash - :deposit_amount, bank = bank + :deposit_amount WHERE id = :id");
        $stmt->bindParam(':deposit_amount', $deposit_amount);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
    }
}

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
        <p>Bank: $<?php echo $user['bank']; ?></p>
        <p>Debt: $<?php echo $user['debt']; ?></p>
        <p>Health: <?php echo $user['health']; ?>%</p>

        <form method="POST">
            <h3>Repay Loan</h3>
            <input type="number" name="repay_amount" placeholder="Amount to repay">
            <button type="submit" class="green-button">Repay</button>
        </form>

        <form method="POST">
            <h3>Get Cash Advance</h3>
            <input type="number" name="cash_advance" placeholder="Amount to borrow">
            <button type="submit" class="green-button">Get Advance</button>
        </form>

        <form method="POST">
            <h3>Deposit Money</h3>
            <input type="number" name="deposit_amount" placeholder="Amount to deposit">
            <button type="submit" class="green-button">Deposit</button>
        </form>

        <button class="green-button" onclick="window.location.href='game.php'">Back to Game</button>
    </div>
</body>
</html>
