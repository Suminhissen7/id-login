<?php
// Database configuration
$host = 'mysql-tobd.alwaysdata.net';
$db   = 'tobd_api';
$user = 'tobd';
$pass = 'shihab067';  // 🔁 এখানে আপনার পাসওয়ার্ড দিন
$table = 'orders';         // 🔁 এখানে আপনার টেবিলের নাম দিন

header('Content-Type: application/json');

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'ডাটাবেইসে কানেক্ট হওয়া যায়নি']);
    exit();
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['order_no'])) {
    http_response_code(400);
    echo json_encode(['error' => 'order_no অনুপস্থিত']);
    exit();
}

$order_no = $conn->real_escape_string($input['order_no']);

// Query the database
$sql = "SELECT player_id, products, datetime, username FROM `$table` WHERE order_no = '$order_no' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'player_id' => $data['player_id'],
        'products'  => $data['products'],
        'datetime'  => $data['datetime'],
        'username'  => $data['username']
    ]);
} else {
    echo json_encode(['error' => 'Order খুঁজে পাওয়া যায়নি']);
}

$conn->close();
?>
