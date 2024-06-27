<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT cash, bank, debt, health FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error_message = '';

// Handle repayments, deposits, and withdrawals
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['repay_amount'])) {
        $repay_amount = (float) $_POST['repay_amount'];
        if ($repay_amount > 0 && $repay_amount <= $user['cash'] && $repay_amount <= $user['debt']) {
            $stmt = $conn->prepare("UPDATE users SET cash = cash - :repay_amount, debt = debt - :repay_amount WHERE id = :id");
            $stmt->bindParam(':repay_amount', $repay_amount);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
        } else {
            $error_message = 'Insufficient funds';
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
            $error_message = 'Insufficient funds';
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
            $error_message = 'Insufficient funds';
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
            $error_message = 'Insufficient funds';
        }
    }

    // Refresh the user data after updates
    $stmt = $conn->prepare("SELECT cash, bank, debt, health FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Finances</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
            animation: blink 1s step-end infinite;
        }

        @keyframes blink {
            50% {
                visibility: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Finances</h1>
        <p>Cash: $<?php echo number_format($user['cash'], 2); ?></p>
        <p>Bank: $<?php echo number_format($user['bank'], 2); ?></p>
        <p>Debt: $<?php echo number_format($user['debt'], 2); ?></p>
        <p>Health: <?php echo $user['health']; ?>%</p>
        <?php if ($error_message): ?>
            <p id="error-message" class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <h3>Repay Loan</h3>
            <input type="number" name="repay_amount" step="0.01" placeholder="Amount to repay" required>
            <button type="submit" class="green-button">Repay</button>
        </form>

        <form method="POST">
            <h3>Get Cash Advance</h3>
            <input type="number" name="cash_advance" step="0.01" placeholder="Amount to borrow" required>
            <button type="submit" class="green-button">Get Advance</button>
        </form>

        <form method="POST">
            <h3>Deposit Money</h3>
            <input type="number" name="deposit_amount" step="0.01" placeholder="Amount to deposit" required>
            <button type="submit" class="green-button">Deposit</button>
        </form>

        <form method="POST">
            <h3>Withdraw Money</h3>
            <input type="number" name="withdraw_amount" step="0.01" placeholder="Amount to withdraw" required>
            <button type="submit" class="green-button">Withdraw</button>
        </form>

        <button class="green-button" onclick="window.location.href='game.php'">Back to Game</button>
    </div>
    <script>
        // Hide the error message after 3 seconds
        if (document.getElementById('error-message')) {
            setTimeout(function() {
                document.getElementById('error-message').style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
