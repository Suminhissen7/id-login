<?php
// Database config
$host = 'mysql-tobd.alwaysdata.net';
$db   = 'tobd_api';
$user = 'tobd';
$pass = 'shihab067';

header('Content-Type: application/json');

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Check if order_no is provided
if (!isset($_POST['order_no'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing order_no']);
    exit();
}

$order_no = $conn->real_escape_string($_POST['order_no']);

// Query the database
$sql = "SELECT player_id, products, datetime, username FROM orders WHERE order_no = '$order_no' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'player_id' => $data['player_id'],
        'products' => $data['products'],
        'datetime' => $data['datetime'],
        'username' => $data['username']
    ]);
} else {
    echo json_encode(['error' => 'Order not found']);
}

$conn->close();
?>
