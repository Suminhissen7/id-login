<?php

header ("Content-Type:application/json; charset=utf-8");

$data = [
    'player_id' => '2415290464',
    'product_id' => 0,
    'payment_method' => 'up_gift_card',
    'serial' => 'UPBD-Q-S-00366359',
    'pin' => '2964-3711-7977-4158'
];

$jsonData = json_encode($data);

$url = 'https://api.rngamingshop.com/unipin-new/top-up';

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData),
    'X-Api-Key: 8fdc3a581fd12d0d6cb8074c8eff6050'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

echo $response;
