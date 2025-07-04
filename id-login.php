<?php

header('Content-Type: application/json');

// Raw POST data পড়া
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// login_id চেক করা
if (!isset($input['login_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'login_id missing']);
    exit;
}

$login_id = $input['login_id'];

// Database কানেকশন
$mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

// চেক করা player আগে থেকেই Database এ আছে কি না
$stmt = $mysqli->prepare("SELECT session_key FROM players WHERE user_id = ?");
$stmt->bind_param("i", $login_id);
$stmt->execute();
$stmt->store_result();

$notify_url = "https://id.tobd.top/ff/?id=" . urlencode($login_id);
$notify_status = json_encode(["error" => true, "msg" => "invalid_uid"]);

if ($stmt->num_rows > 0) {
    // যদি player আগে থেকেই থাকে তাহলে সরাসরি Notify API কল করবো
    $stmt->bind_result($existing_session_key);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    // Notify call
    $notify_curl = curl_init();
    curl_setopt_array($notify_curl, [
        CURLOPT_URL => $notify_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'X-Api-Key: 8fdc3a581fd12d0d6cb8074c8eff5050',
        ],
    ]);
    $notify_response = curl_exec($notify_curl);
    $notify_error = curl_error($notify_curl);
    curl_close($notify_curl);

    echo $notify_error ?: $notify_response;
    exit;
}

// যদি না থাকে তাহলে Garena API তে রিকোয়েস্ট পাঠানো হবে
$stmt->close();

// Garena API কল
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://shop.garena.my/api/auth/player_id_login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_TIMEOUT => 30,
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
        'Pragma: no-cache',
        'Referer: https://shop.garena.my/?app=100067&item=100712&channel=202278',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "Android"',    ],
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
$open_id = $response_data['open_id'] ?? null;

// Cookie থেকে session_key খুঁজে বের করা
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

// session_key পেলে Database এ Save করে Notify করবো
if ($session_key) {
    $stmt = $mysqli->prepare("INSERT INTO players (user_id, session_key) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), updated_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("is", $login_id, $session_key);

    if ($stmt->execute()) {
        // Notify API কল
        $notify_curl = curl_init();
        curl_setopt_array($notify_curl, [
            CURLOPT_URL => $notify_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: 8fdc3a581fd12d0d6cb8074c8eff5050',
            ],
        ]);
        $notify_response = curl_exec($notify_curl);
        $notify_error = curl_error($notify_curl);
        curl_close($notify_curl);

        echo $notify_error ?: $notify_response;
    } else {
        echo json_encode(['error' => 'DB save failed']);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => true, "msg" => "session_key not found"]);
}

$mysqli->close();
