<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-Type: application/json');

/**
 * দ্বিতীয় API (id.tobd.top) কল করার জন্য একটি ফাংশন।
 *
 * @param string|int $login_id প্লেয়ারের আইডি।
 * @return string API থেকে প্রাপ্ত ফলাফল।
 */
function notifySecondAPI($login_id) {
    $notify_url = "https://id.tobd.top/ff/?id=" . urlencode($login_id);
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

    return $notify_error ?: $notify_response;
}

// Raw POST data থেকে login_id পড়া
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// login_id ইনপুটে আছে কিনা তা পরীক্ষা করা
if (!isset($input['login_id']) || empty($input['login_id'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'msg' => 'login_id is missing or empty']);
    exit;
}

$login_id = $input['login_id'];

// --- ধাপ ১: ডাটাবেস কানেকশন এবং প্লেয়ার আইডি চেক ---
$mysqli = new mysqli('mysql-tobd.alwaysdata.net', 'tobd', 'shihab067', 'tobd_api');

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => true, 'msg' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

// ডাটাবেসে প্লেয়ার আইডি খোঁজা হচ্ছে
$stmt = $mysqli->prepare("SELECT session_key FROM players WHERE user_id = ?");
$stmt->bind_param("s", $login_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// যদি প্লেয়ার আইডি ডাটাবেসে পাওয়া যায়
if ($result->num_rows > 0) {
    $mysqli->close();
    // সরাসরি দ্বিতীয় API কল করা হচ্ছে
    $notify_status = notifySecondAPI($login_id);
    echo $notify_status;
    exit;
}

// --- ধাপ ২: প্লেয়ার আইডি ডাটাবেসে না পাওয়া গেলে Garena API কল ---
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
    // আপনার দেওয়া সম্পূর্ণ হেডার এখানে যুক্ত করা হলো
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
        'sec-ch-ua-platform: "Android"',
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if ($err) {
    http_response_code(500);
    echo json_encode(['error' => true, 'msg' => 'Garena API call failed: ' . $err]);
    curl_close($curl);
    $mysqli->close();
    exit;
}

$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($curl);

$response_data = json_decode($body, true);
if (isset($response_data['error'])) {
    echo json_encode(['error' => true, 'msg' => 'Invalid UID according to Garena: ' . $response_data['error']]);
    $mysqli->close();
    exit;
}

// হেডার থেকে session_key বের করা
$session_key = null;
if (preg_match('/^Set-Cookie:\s*session_key=([^;]*)/mi', $header, $matches)) {
    $session_key = $matches[1];
}

$notify_status = json_encode([
    "error" => true,
    "msg" => "invalid_uid_or_session_key_not_found"
]);

// --- ধাপ ৩: session_key পাওয়া গেলে ডাটাবেসে সেভ এবং দ্বিতীয় API কল ---
if ($session_key) {
    $stmt = $mysqli->prepare("INSERT INTO players (user_id, session_key) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_key = VALUES(session_key), updated_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("ss", $login_id, $session_key);
    $stmt->execute();
    $stmt->close();
    
    sleep(1);

    $notify_status = notifySecondAPI($login_id);
}

$mysqli->close();

echo $notify_status;

?>
