<?php

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://shop.garena.my/api/auth/player_id_login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        "app_id" => 100067,
        "login_id" => "7584754202"
    ]),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: en-US,en;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Content-Type: application/json',
        'Cookie: region=MY; mspid2=1c2373c9661dffbc747b841fa22e9af8; language=en; _ga=GA1.1.1051086503.1742954649; source=mb; datadome=03dZTkdI4~zMISDX0hm9wpgq8nDwLFZmvebIl15X7bPnexVKaXGjqLyLccmby7QP6tJoKM29zwhJLuG7qYc6_GyU4ZxxYjInSOd5q~6KVbykT4LGVceB5YyIyKUr2gRJ; session_key=840ru8xwrnhz7k9bisvqg62qh7mm1lcd; _ga_9F1KGGRJHY=GS1.1.1745094802.71.1.1745094844.0.0.0',
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

curl_close($curl);

if ($err) {
    echo "cURL Error #: " . $err;
} else {
    echo $response;
}
