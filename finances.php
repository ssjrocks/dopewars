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
        <p id="cash">Cash: $<?php echo number_format($user['cash'], 2); ?></p>
        <p id="bank">Bank: $<?php echo number_format($user['bank'], 2); ?></p>
        <p id="debt">Debt: $<?php echo number_format($user['debt'], 2); ?></p>
        <p>Health: <?php echo $user['health']; ?>%</p>
        <p id="error-message" class="error-message" style="display: none;"></p>

        <form id="repay-form" method="POST">
            <h3>Repay Loan</h3>
            <input type="number" name="repay_amount" step="0.01" placeholder="Amount to repay" required>
            <button type="submit" class="green-button">Repay</button>
        </form>

        <form id="advance-form" method="POST">
            <h3>Get Cash Advance</h3>
            <input type="number" name="cash_advance" step="0.01" placeholder="Amount to borrow" required>
            <button type="submit" class="green-button">Get Advance</button>
        </form>

        <form id="deposit-form" method="POST">
            <h3>Deposit Money</h3>
            <input type="number" name="deposit_amount" step="0.01" placeholder="Amount to deposit" required>
            <button type="submit" class="green-button">Deposit</button>
        </form>

        <form id="withdraw-form" method="POST">
            <h3>Withdraw Money</h3>
            <input type="number" name="withdraw_amount" step="0.01" placeholder="Amount to withdraw" required>
            <button type="submit" class="green-button">Withdraw</button>
        </form>

        <button class="green-button" onclick="window.location.href='game.php'">Back to Game</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function handleFormSubmission(formId) {
                const form = document.getElementById(formId);
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const formData = new FormData(form);
                    fetch('handle_finances.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'error') {
                            showError(data.message);
                        } else {
                            updateUserInfo(data.user);
                        }
                    });
                });
            }

            function showError(message) {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }

            function updateUserInfo(user) {
                document.getElementById('cash').textContent = `Cash: $${parseFloat(user.cash).toFixed(2)}`;
                document.getElementById('bank').textContent = `Bank: $${parseFloat(user.bank).toFixed(2)}`;
                document.getElementById('debt').textContent = `Debt: $${parseFloat(user.debt).toFixed(2)}`;
            }

            handleFormSubmission('repay-form');
            handleFormSubmission('advance-form');
            handleFormSubmission('deposit-form');
            handleFormSubmission('withdraw-form');
        });
    </script>
</body>
</html>
