<?php

set_time_limit(0);
// Fungsi untuk melakukan permintaan cURL
function makeCurlRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Api-key: 8fdc3a581fd12d0d6cb8074c8eff6050'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// URL API
$url = "https://api.rngamingshop.com/login-id-my/?id=2415290464";
$currentUtcTime = new DateTime("now", new DateTimeZone("UTC"));

// Atur header untuk output buffering
header('Content-Type: text/html');
ob_implicit_flush(true);
ob_end_flush(); // Menonaktifkan output buffering (jika ada)

for ($i = 0; $i < 100; $i++) { // Misalkan permintaan dilakukan 5 kali
    echo "<p>Request -".($i+1)." ".$currentUtcTime->format("Y-m-d H:i:s")." UTC</p>";

    // Lakukan permintaan cURL
    $response = makeCurlRequest($url);

    echo "<p>".$response."</p>";

    // Gunakan flush() untuk mengirim output secara langsung ke browser
    flush();
}

echo "<p>Done!</p>";
?>
