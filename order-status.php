<?php
// Database configuration
$host = 'mysql-tobd.alwaysdata.net';
$db   = 'tobd_api';
$user = 'tobd';
$pass = 'shihab067';
$table = 'orders';

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
$sql = "SELECT order_no, player_id, products, datetime, username, status FROM `$table` WHERE order_no = '$order_no' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    
    $text .= "✥➳ Your Order Is: {$data['status']}\n";
    $text .= "✥➳ ID: {$data['order_no']}\n";
    $text .= "✥➳ UID: {$data['player_id']}\n";
    $text .= "✥➳ Name: " . ($data['username'] ?? 'N/A') . "\n";
    $text .= "✥➳ Product: " . ($data['products'] ?? 'N/A') . "\n";
    $text .= "✥➳ Date/Time: {$data['datetime']}";
    

    echo json_encode([
        'text' => $text,
        'data' => [
            'id'         => $data['order_no'],
            'Uid'        => $data['player_id'],
            'name'       => $data['username'],
            'Product'    => $data['products'],
            'Date Time'  => $data['datetime'],
            'Status'     => $data['status']
        ]
    ]);
} else {
    echo json_encode(['text' => '🥺 Order id not Found 🥺']);
}

$conn->close();
?>
