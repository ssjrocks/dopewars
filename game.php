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

// Fetch goods
$goods_stmt = $conn->prepare("SELECT * FROM goods");
$goods_stmt->execute();
$goods = $goods_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations
$locations_stmt = $conn->prepare("SELECT * FROM locations");
$locations_stmt->execute();
$locations = $locations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Assume current location is stored in session or user data for simplicity
$current_location_id = $user['location_id'];

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
            $error_message = "You wanna sell nothing?";
            break;
        case 'insufficient_inventory':
            $error_message = "You don't have enough inventory.";
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>DopeWars</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script>
        function selectItem(event) {
            var items = document.querySelectorAll('.selectable');
            items.forEach(item => item.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            document.querySelector('input[name="good_id"]').value = event.currentTarget.dataset.id;
        }
    </script>
</head>
<body>
    <div class="game-container">
        <div class="header">
            <h1>DopeWars</h1>
        </div>
        <div class="status">
            <div>Cash: $<?php echo $user['cash']; ?></div>
            <div>Bank: $<?php echo $user['bank']; ?></div>
            <div>Debt: $<?php echo $user['debt']; ?></div>
            <div>Guns: 0</div>
            <div>Health: 100%</div>
        </div>
        <div class="locations">
            <h2>Subway from:</h2>
            <?php foreach ($locations as $location): ?>
                <form method="POST" action="travel.php" style="display: inline;">
                    <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                    <button class="<?php echo $location['id'] == $current_location_id ? 'current-location' : 'location-button'; ?>">
                        <?php echo $location['name']; ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
        <div class="main-content">
            <div class="goods">
                <h2>Available Drugs:</h2>
                <?php if ($error_message): ?>
                    <p class="error"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <ul>
                    <?php foreach ($goods as $good): ?>
                        <li class="selectable" data-id="<?php echo $good['id']; ?>" onclick="selectItem(event)">
                            <?php echo $good['name'] . " - $" . rand($good['min_price'], $good['max_price']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="buy-buttons">
                    <form method="POST" action="buy.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="1">
                        <button class="green-button">Buy One</button>
                    </form>
                    <form method="POST" action="buy.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="2">
                        <button class="green-button">Buy Two</button>
                    </form>
                    <form method="POST" action="buy.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="3">
                        <button class="green-button">Buy Three</button>
                    </form>
                </div>
            </div>
            <div class="inventory">
                <h2>Trenchcoat: Usage 0/100</h2>
                <ul>
                    <!-- Inventory items will be populated here -->
                </ul>
                <div class="sell-buttons">
                    <form method="POST" action="sell.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="1">
                        <button class="green-button">Sell One</button>
                    </form>
                    <form method="POST" action="sell.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="2">
                        <button class="green-button">Sell Two</button>
                    </form>
                    <form method="POST" action="sell.php">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="3">
                        <button class="green-button">Sell Three</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="finances-button">
            <button class="green-button" onclick="window.location.href='finances.php'">Finances</button>
        </div>
        <div class="footer">
            <form method="POST" action="new_game.php">
                <button type="submit" class="green-button">New Game</button>
            </form>
            <button class="green-button" onclick="window.location.href='login.php'">Exit</button>
        </div>
    </div>
</body>
</html>
