<?php
echo "<h3>1. Network Download Speed Test</h3>";

$fileUrl = 'http://cachefly.cachefly.net/10mb.test'; // 10MB test file
$fileSizeMB = 10;

// `allow_url_fopen` চালু আছে কিনা তা পরীক্ষা করা হচ্ছে
if (!ini_get('allow_url_fopen')) {
    echo "Error: 'allow_url_fopen' is not enabled in your php.ini file.";
} else {
    $startTime = microtime(true);
    file_get_contents($fileUrl);
    $endTime = microtime(true);

    $duration = $endTime - $startTime;
    $speedMbps = ($fileSizeMB * 8) / $duration; // Megabytes to Megabits

    echo "Time taken to download 10MB file: " . number_format($duration, 2) . " seconds<br>";
    echo "Approximate Download Speed: <strong>" . number_format($speedMbps, 2) . " Mbps</strong>";
}
echo "<hr>";
?>
