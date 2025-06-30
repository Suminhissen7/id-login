<?php
// Telegram Bot Token
$botToken = "7593489434:AAHRlib7W6k8kyVFZ1yMmwk4lFoNPx6ASNE";

// JSON POST থেকে ডেটা গ্রহণ
$data = json_decode(file_get_contents('php://input'), true);

// চেক করে নিচ্ছে যে চ্যাট আইডি ও মেসেজ আছে কিনা
if (!isset($data['chat_id']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'chat_id and message required']);
    exit;
}

// মেসেজ ও চ্যাট আইডি রিড করা
$chatId = $data['chat_id'];
$message = $data['message'];

// Telegram API Endpoint
$url = "https://api.telegram.org/bot$botToken/sendMessage";

// POST ডেটা প্রস্তুত
$postFields = [
    'chat_id' => $chatId,
    'text' => $message
];

// cURL ব্যবহার করে POST রিকোয়েস্ট পাঠানো
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));

$response = curl_exec($ch);
curl_close($ch);

// রেসপন্স ফিরিয়ে দেওয়া
echo $response;
?>