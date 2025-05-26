<?php
$running = 0;
$max = 200; // কতগুলো চেষ্টা করবে
$outputFile = "run.txt";

for ($i = 0; $i < $max; $i++) {
    $pid = exec("sleep 5 > /dev/null & echo $!");
    if ($pid && is_numeric($pid)) {
        $running++;
    } else {
        break;
    }
}

// রেজাল্ট run.txt ফাইলে লিখে ফেলবে
file_put_contents($outputFile, "Max exec() processes allowed: $running");

echo "Test completed. Result saved in run.txt\n";
?>
