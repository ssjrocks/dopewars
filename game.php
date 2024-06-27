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

?>
<!DOCTYPE html>
<html>
<head>
    <title>DopeWars</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
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
            <h2>Subway from Bronx:</h2>
            <?php foreach ($locations as $location): ?>
                <button><?php echo $location['name']; ?></button>
            <?php endforeach; ?>
        </div>
        <div class="goods">
            <h2>Available Drugs:</h2>
            <table>
                <tr>
                    <th>Drug</th>
                    <th>Price</th>
                </tr>
                <?php foreach ($goods as $good): ?>
                    <tr>
                        <td><?php echo $good['name']; ?></td>
                        <td><?php echo rand($good['min_price'], $good['max_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="actions">
            <button>Buy</button>
            <button>Sell</button>
            <button>Finances</button>
        </div>
        <div class="inventory">
            <h2>Trenchcoat. Space: 100/100</h2>
            <table>
                <tr>
                    <th>Drug</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
                <!-- Inventory items will be populated here -->
            </table>
        </div>
        <div class="footer">
            <button>New Game</button>
            <button>Exit</button>
        </div>
    </div>
</body>
</html>
