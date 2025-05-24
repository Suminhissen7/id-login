<?php

// $_GET থেকে login_id পড়া
if (!isset($_GET['uid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'login_id missing']);
    exit;
}

$login_id = $_GET['uid'];

// Garena API তে রিকোয়েস্ট
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://shop.garena.my/api/auth/player_id_login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        "app_id" => 100067,
        "login_id" => $login_id
    ]),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Content-Type: application/json',
        'Origin: https://shop.garena.my',
        'Referer: https://shop.garena.my/?app=100067&item=100712&channel=202278',
        'User-Agent: Mozilla/5.0',
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if ($err) {
    http_response_code(500);
    echo json_encode(['error' => $err]);
    curl_close($curl);
    exit;
}

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

curl_close($curl);

// Body থেকে JSON ডাটা পড়া
$response_data = json_decode($body, true);

if (isset($response_data['error'])) {
    // invalid_id বা অন্য কোনো error
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid login_id',
        'details' => $response_data['error']
    ]);
    exit;
}

if (!isset($response_data['region']) || $response_data['region'] !== 'BD') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Region is not BD',
        'region' => $response_data['region'] ?? 'unknown'
    ]);
    exit;
}

// সব ঠিক থাকলে বাকি প্রসেসিং করবেন
$open_id = $response_data['open_id'] ?? null;
$session_key = null;

// session_key extraction as before...
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
foreach ($matches[1] as $cookie) {
    if (stripos($cookie, 'session_key=') !== false) {
        $parts = explode('=', $cookie, 2);
        if (count($parts) == 2) {
            $session_key = $parts[1];
        }
        break;
    }
}

// তারপর বাকি ডাটাবেজে সেভ ইত্যাদি

$db_status = "No session_key found";

// যদি session_key থাকে তাহলে Database এ save করবো
if ($session_key) {
    // Database কানেকশন
    $mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');

    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
        exit;
    }

    // Players টেবিলে Insert or Update করা
    $stmt = $mysqli->prepare("INSERT INTO players (user_id, session_key) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), updated_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("is", $login_id, $session_key);

    if ($stmt->execute()) {
        $db_status = "successfully";
    } else {
        $db_status = "Database save/update failed: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
}

// Final Response
header('Content-Type: application/json');
echo json_encode([
    'status' => $db_status,
    'region' => $region,
    'open_id' => $open_id,
    'nickname' => $nickname
]);
