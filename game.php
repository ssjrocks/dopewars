<?php 
include 'db.php';
include 'gamecode.php';
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
        .selected {
            background-color: #ddd;
        }
    </style>
    <script src="game.js"></script>
</head>
<body>
    <div class="game-container">
        <div class="header">
            <h1>DopeWars</h1>
        </div>
        <div class="status">
            <div>Cash: $<span id="cash"><?php echo number_format($user['cash'], 2); ?></span></div>
            <div>Bank: $<span id="bank"><?php echo number_format($user['bank'], 2); ?></span></div>
            <div>Debt: $<span id="debt"><?php echo number_format($user['debt'], 2); ?></span></div>
            <div>Guns: 0</div>
            <div>Health: 100%</div>
        </div>
        <div class="locations">
            <h2>Subway from: <span id="current-location"><?php echo $current_location; ?></span></h2>
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
                <ul id="available-goods">
                    <?php foreach ($goods as $good): ?>
                        <li class="selectable" data-id="<?php echo $good['id']; ?>" onclick="selectItem(event, 'available')">
                            <?php echo $good['name'] . " - $" . rand($good['min_price'], $good['max_price']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="buy-buttons">
                    <form id="buy-form" method="POST">
                        <input type="hidden" name="good_id" value="">
                        <input type="hidden" name="quantity" value="">
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(1)">Buy 1</button>
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(10)">Buy 10</button>
                        <button type="button" class="green-button" onclick="setQuantityAndBuy(25)">Buy 25</button>
                    </form>
                </div>
            </div>
            <div class="inventory">
                <h2>Trenchcoat: Usage <span id="trenchcoat-usage">0</span>/100</h2>
                <ul id="inventory-list">
                    <?php foreach ($inventory as $item): ?>
                        <li class="selectable" data-id="<?php echo $item['id']; ?>" onclick="selectItem(event, 'inventory')">
                            <?php echo $item['name'] . " - " . $item['quantity'] . " @ $" . number_format($item['average_price'], 2); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="sell-buttons">
                    <form id="sell-form" method="POST">
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
</body>
</html>