<?php
// db connection
$pdo = new PDO("mysql:host=mysql-tobd.alwaysdata.net;dbname=tobd_api;charset=utf8", "tobd", "shihab067");

// Telegram Bot Token
$botToken = "7593489434:AAHRlib7W6k8kyVFZ1yMmwk4lFoNPx6ASNE";

// Mapping for pid conversion
$pid_map = [
    20 => 0, 36 => 1, 80 => 2, 160 => 3,
    405 => 4, 810 => 5, 1625 => 6, 161 => 7,
    800 => 8, 162 => 9
];

// Get Telegram message (webhook)
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update['message']['text'])) exit;

$message = $update['message']['text'];
$chatID = $update['message']['chat']['id'];

// Match all serials and pins
preg_match_all('/([A-Z0-9\-]{10,})\s+([\d\-]{10,})/', $message, $matches);

$successCount = 0;
$failSerials = [];

if (!empty($matches[1]) && !empty($matches[2])) {
    $serials = $matches[1];
    $pins = $matches[2];

    // Extract PID value
    if (preg_match('/✓\s*(\d+)\s*🆄︎🅲︎/', $message, $pid_match)) {
        $raw_pid = intval($pid_match[1]);
        $pid = $pid_map[$raw_pid] ?? null;

        if ($pid !== null) {
            
            // *** START: সময় মাপার জন্য কোড যোগ করা হয়েছে ***
            $startTime = microtime(true); // ডেটাবেস অপারেশন শুরুর সময় রেকর্ড
            // *** END: সময় মাপার কোড ***

            foreach ($serials as $index => $serial) {
                $type = substr($serial, 0, 4);
                $pin = $pins[$index];

                try {
                    $stmt = $pdo->prepare("INSERT INTO vouchers (pid, type, serial, pin) VALUES (?, ?, ?, ?)");
                    $success = $stmt->execute([$pid, $type, $serial, $pin]);

                    if ($success) {
                        $successCount++;
                    } else {
                        $failSerials[] = $serial;
                    }
                } catch (Exception $e) {
                    $failSerials[] = $serial;
                }
            }

            // *** START: সময় মাপার এবং মেসেজ পাঠানোর কোড পরিবর্তন করা হয়েছে ***
            $endTime = microtime(true); // ডেটাবেস অপারেশন শেষের সময় রেকর্ড
            $duration = $endTime - $startTime; // মোট সময় গণনা (সেকেন্ডে)

            // Build confirmation message
            $confirmationMessage = "{$successCount} success";

            if (!empty($failSerials)) {
                $confirmationMessage .= "\nসেভ হয়নি: " . implode(', ', $failSerials);
            }

            // মেসেজের সাথে মোট সময় যোগ করা
            $confirmationMessage .= "\n\nসময় লেগেছে: " . number_format($duration, 3) . " সেকেন্ড।";
            
            sendTelegramMessage($botToken, $chatID, $confirmationMessage);
            // *** END: পরিবর্তিত কোড ***

        } else {
            sendTelegramMessage($botToken, $chatID, "PID ($raw_pid) সঠিক নয় বা ম্যাপ করা যায়নি।");
        }
    } else {
        sendTelegramMessage($botToken, $chatID, "PID তথ্য পাওয়া যায়নি।");
    }
}

// Function to send message to Telegram
function sendTelegramMessage($botToken, $chatID, $message) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatID,
        'text' => $message,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>
