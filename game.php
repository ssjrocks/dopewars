// game.php
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
    <style>
        /* Add your CSS styling here */
    </style>
</head>
<body>
    <h1>Welcome, <?php echo $user['username']; ?>!</h1>
    <p>Cash: $<?php echo $user['cash']; ?></p>
    
    <h2>Goods</h2>
    <table>
        <tr>
            <th>Good</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php foreach ($goods as $good): ?>
            <tr>
                <td><?php echo $good['name']; ?></td>
                <td><?php echo rand($good['min_price'], $good['max_price']); ?></td>
                <td>
                    <form method="POST" action="buy.php">
                        <input type="hidden" name="good_id" value="<?php echo $good['id']; ?>">
                        <button type="submit">Buy</button>
                    </form>
                    <form method="POST" action="sell.php">
                        <input type="hidden" name="good_id" value="<?php echo $good['id']; ?>">
                        <button type="submit">Sell</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Locations</h2>
    <ul>
        <?php foreach ($locations as $location): ?>
            <li><?php echo $location['name']; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
