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
$current_location_id = 1; // Example current location id

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
            <div>Bank: $0</div>
            <div>Debt: $5,500</div>
            <div>Guns: 0</div>
            <div>Health: 100%</div>
        </div>
        <div class="locations">
            <h2>Subway from:</h2>
            <?php foreach ($locations as $location): ?>
                <form method="POST" action="travel.php" style="display: inline;">
                    <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                    <button class="<?php echo $location['id'] == $current_location_id ? 'current-location' : ''; ?>">
                        <?php echo $location['name']; ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
        <div class="main-content">
            <div class="goods">
                <h2>Available Drugs:</h2>
                <ul>
                    <?php foreach ($goods as $good): ?>
                        <li class="selectable" onclick="selectItem(event)">
                            <?php echo $good['name'] . " - $" . rand($good['min_price'], $good['max_price']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="buy-buttons">
                    <form method="POST" action="buy.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="1">
                        <button>Buy One</button>
                    </form>
                    <form method="POST" action="buy.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="2">
                        <button>Buy Two</button>
                    </form>
                    <form method="POST" action="buy.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="3">
                        <button>Buy Three</button>
                    </form>
                </div>
            </div>
            <div class="inventory">
                <h2>Trenchcoat: Usage 0/100</h2>
                <ul>
                    <!-- Inventory items will be populated here -->
                </ul>
                <div class="sell-buttons">
                    <form method="POST" action="sell.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="1">
                        <button>Sell One</button>
                    </form>
                    <form method="POST" action="sell.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="2">
                        <button>Sell Two</button>
                    </form>
                    <form method="POST" action="sell.php" style="display: inline;">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="3">
                        <button>Sell Three</button>
                    </form>
                </div>
                <div class="finance-button">
                    <button onclick="window.location.href='finances.php'">Finances</button>
                </div>
            </div>
        </div>
        <div class="footer">
            <button>New Game</button>
            <button>Exit</button>
        </div>
    </div>
</body>
</html>
