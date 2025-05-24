<?php

if (!isset($_GET['login_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'login_id missing']);
    exit;
}

$login_id = $_GET['login_id'];

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
        'Pragma: no-cache',
        'Referer: https://shop.garena.my/?app=100067&item=100712&channel=202278',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "Android"',
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

$response_data = json_decode($body, true);

// region check
if (!isset($response_data['region'])) {
    echo json_encode(['status' => 'invalid_id']);
    exit;
}

$region = strtoupper($response_data['region']);

if ($region !== 'BD') {
    echo json_encode(['status' => 'region_not_bd']);
    exit;
}

// session_key extract from header
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

// যদি session_key না পাওয়া যায়
if (!$session_key) {
    echo json_encode(['status' => 'no_session_key']);
    exit;
}

// session_key থাকলে DB-তে save করি
$mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');

if (!$mysqli->connect_error) {
    $stmt = $mysqli->prepare("INSERT INTO players (user_id, session_key, nickname, open_id, img_url) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), nickname = VALUES(nickname), open_id = VALUES(open_id), img_url = VALUES(img_url), updated_at = CURRENT_TIMESTAMP");
    $nickname = $response_data['nickname'] ?? '';
    $open_id = $response_data['open_id'] ?? '';
    $img_url = $response_data['img_url'] ?? '';
    $stmt->bind_param("issss", $login_id, $session_key, $nickname, $open_id, $img_url);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// সফল রেসপন্স
echo json_encode([
    'status' => 'success',
    'nickname' => $response_data['nickname'] ?? '',
    'open_id' => $response_data['open_id'] ?? '',
    'img_url' => $response_data['img_url'] ?? ''
]);
