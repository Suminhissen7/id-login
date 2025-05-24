<?php

if (!isset($_GET['uid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'login_id missing']);
    exit;
}

$login_id = $_GET['uid'];

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
        'Cookie: region=MY; mspid2=1c2373c9661dffbc747b841fa22e9af8; language=en; _ga=GA1.1.1051086503.1742954649; source=mb; datadome=03dZTkdI4~zMISDX0hm9wpgq8nDwLFZmvebIl15X7bPnexVKaXGjqLyLccmby7QP6tJoKM29zwhJLuG7qYc6_GyU4ZxxYjInSOd5q~6KVbykT4LGVceB5YyIyKUr2gRJ; session_key=wjh9ahie6snagyxnfw7ohbjs32uvuvv6; _ga_9F1KGGRJHY=GS1.1.1745094802.71.1.1745094844.0.0.0',
        'Origin: https://shop.garena.my',
        'Referer: https://shop.garena.my/?app=100067&item=100712&channel=202278',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if ($err) {
    http_response_code(500);
    echo json_encode([
        'error' => 'cURL failed',
        'details' => $err
    ]);
    curl_close($curl);
    exit;
}

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($curl);

// JSON Decode
$response_data = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Invalid JSON from API',
        'json_error' => json_last_error_msg(),
        'body' => $body
    ]);
    exit;
}

// Region Check
if (!isset($response_data['region'])) {
    echo json_encode(['status' => 'invalid_id']);
    exit;
}

$region = strtoupper($response_data['region']);
if ($region !== 'BD') {
    echo json_encode(['status' => 'region_not_bd']);
    exit;
}

// Session key extract
$session_key = null;
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
if (!$session_key) {
    echo json_encode(['status' => 'no_session_key']);
    exit;
}

// Database
$mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');
if ($mysqli->connect_error) {
    echo json_encode(['error' => 'DB connection failed', 'details' => $mysqli->connect_error]);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO players (user_id, session_key, nickname, open_id, img_url) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), nickname = VALUES(nickname), open_id = VALUES(open_id), img_url = VALUES(img_url), updated_at = CURRENT_TIMESTAMP");

if (!$stmt) {
    echo json_encode(['error' => 'DB Prepare failed', 'details' => $mysqli->error]);
    exit;
}

$nickname = $response_data['nickname'] ?? '';
$open_id = $response_data['open_id'] ?? '';
$img_url = $response_data['img_url'] ?? '';

$stmt->bind_param("sssss", $login_id, $session_key, $nickname, $open_id, $img_url);
$stmt->execute();

if ($stmt->error) {
    echo json_encode(['error' => 'DB Execute failed', 'details' => $stmt->error]);
    exit;
}

$stmt->close();
$mysqli->close();

// Success
echo json_encode([
    'status' => 'success',
    'nickname' => $nickname,
    'open_id' => $open_id,
    'img_url' => $img_url
]);
