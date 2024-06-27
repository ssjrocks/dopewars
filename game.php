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
            $error_message = "Sell what, your soul?";
            break;
        case 'insufficient_inventory':
            $error_message = "Hey you tryna stiff me?";
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>DopeWars</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
            display: none;
        }
        .hidden-placeholder {
            visibility: hidden;
        }
    </style>
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
            <select id="location-select">
                <?php foreach ($locations as $location): ?>
                    <option value="<?php echo $location['id']; ?>" <?php echo $location['id'] == $current_location_id ? 'selected' : ''; ?>>
                        <?php echo $location['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button onclick="travel()">Go</button>
        </div>
        <div id="error-placeholder" class="hidden-placeholder">Error Placeholder</div>
        <div id="error-message" class="error-message"><?php echo $error_message; ?></div>
        <div class="main-content">
            <div class="goods">
                <h2>Available Drugs:</h2>
                <ul>
                    <?php foreach ($goods as $good): ?>
                        <li class="selectable" data-id="<?php echo $good['id']; ?>" onclick="selectItem(event)">
                            <?php echo $good['name'] . " - $" . rand($good['min_price'], $good['max_price']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="buy-buttons">
                    <form id="buy-form" method="POST" onsubmit="buy(event)">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="">
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(1)">Buy 1</button>
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(10)">Buy 10</button>
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(25)">Buy 25</button>
                    </form>
                </div>
            </div>
            <div class="inventory">
                <h2>Trenchcoat: Usage 0/100</h2>
                <ul id="inventory-list">
                    <!-- Inventory items will be populated here -->
                </ul>
                <div class="sell-buttons">
                    <form id="sell-form" method="POST" onsubmit="sell(event)">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="">
                        <button type="button" class="green-button" onclick="setQuantityAndSell(1)">Sell 1</button>
                        <button type="button" class="green-button" onclick="setQuantityAndSell(10)">Sell 10</button>
                        <button type="button" class="green-button" onclick="setQuantityAndSell(25)">Sell 25</button>
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
    <script>
        function selectItem(event) {
            var items = document.querySelectorAll('.selectable');
            items.forEach(item => item.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            document.querySelector('#buy-form input[name="good_id"]').value = event.currentTarget.dataset.id;
            document.querySelector('#sell-form input[name="good_id"]').value = event.currentTarget.dataset.id;
        }

        function travel() {
            const locationId = document.getElementById('location-select').value;
            fetch('travel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `location_id=${locationId}`
            })
            .then(response => response.text())
            .then(() => {
                location.reload();
            });
        }

        function setQuantityAndBuy(quantity) {
            document.querySelector('#buy-form input[name="quantity"]').value = quantity;
            document.getElementById('buy-form').submit();
        }

        function setQuantityAndSell(quantity) {
            document.querySelector('#sell-form input[name="quantity"]').value = quantity;
            document.getElementById('sell-form').submit();
        }

        function buy(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('buy-form'));
            fetch('buy.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    showError(data.message);
                } else {
                    updateUserInfo(data.user);
                    updateInventory(data.inventory);
                }
            });
        }

        function sell(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('sell-form'));
            fetch('sell.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    showError(data.message);
                } else {
                    updateUserInfo(data.user);
                    updateInventory(data.inventory);
                }
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
            document.querySelector('.status div:nth-child(1)').textContent = `Cash: $${user.cash}`;
            document.querySelector('.status div:nth-child(2)').textContent = `Bank: $${user.bank}`;
            document.querySelector('.status div:nth-child(3)').textContent = `Debt: $${user.debt}`;
        }

        function updateInventory(inventory) {
            const inventoryList = document.getElementById('inventory-list');
            inventoryList.innerHTML = '';
            inventory.forEach(item => {
                const li = document.createElement('li');
                li.textContent = `${item.name} - ${item.quantity}`;
                inventoryList.appendChild(li);
            });
        }
    </script>
</body>
</html>
