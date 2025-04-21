<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: User input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['login_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'login_id missing']);
    exit;
}

$login_id = $input['login_id'];

// Step 2: Call Garena API
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://shop.garena.my/api/auth/player_id_login",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'app_id' => 100067,
        'login_id' => $login_id
    ]),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json',
        'Origin: https://shop.garena.my',
        'Referer: https://shop.garena.my/?app=100067&item=100712&channel=202278',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36'
    ],
    CURLOPT_HEADER => true // important to capture headers
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    http_response_code(500);
    echo json_encode(['error' => 'Curl error: ' . curl_error($curl)]);
    exit;
}

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

curl_close($curl);

// Step 3: Parse Garena Response
$body_json = json_decode($body, true);
$open_id = $body_json['open_id'] ?? null;

// Extract session_key from headers
preg_match('/session_key=([^;]+)/', $header, $matches);
$session_key = $matches[1] ?? null;

// Step 4: Decision
if ($open_id && $session_key) {
    // Open_id এবং Session key পাওয়া গেছে

    // Database Connect
    $mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');

    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO players (login_id, session_key) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), updated_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("is", $login_id, $session_key);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Database insert/update failed: ' . $stmt->error]);
        exit;
    }

    $stmt->close();
    $mysqli->close();
    
    // Now call Towmia API
    $towmia_url = "https://towmia.me/ff/?id=" . urlencode($login_id);
    
    $towmia_curl = curl_init();
    curl_setopt_array($towmia_curl, [
        CURLOPT_URL => $towmia_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);

    $towmia_response = curl_exec($towmia_curl);

    if (curl_errno($towmia_curl)) {
        http_response_code(500);
        echo json_encode(['error' => 'Towmia API error: ' . curl_error($towmia_curl)]);
        exit;
    }

    curl_close($towmia_curl);

    // Show only Towmia response
    echo $towmia_response;

} else {
    // open_id বা session_key না পাওয়া গেলে Garena API response দেখাবে
    http_response_code(200);
    echo $body; // পুরা Garena body রেসপন্স দেখাবে
}
