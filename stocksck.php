<?php
// হেডার সেট করা হচ্ছে যাতে আউটপুট JSON ফরম্যাটে হয়
header("Content-Type: application/json; charset=UTF-8");

// ডাটাবেস কানেকশনের জন্য প্রয়োজনীয় তথ্য
$servername = "mysql-tobd.alwaysdata.net"; // আপনার সার্ভারের নাম, সাধারণত localhost হয়
$username = "tobd";      // আপনার ডাটাবেসের ইউজারনেম
$password = "shihab067";          // আপনার ডাটাবেসের পাসওয়ার্ড
$dbname = "tobd_api"; // আপনার ডাটাবেসের নাম

// ডাটাবেসের সাথে কানেকশন তৈরি করা হচ্ছে
$conn = new mysqli($servername, $username, $password, $dbname);

// কানেকশন ঠিক আছে কিনা তা পরীক্ষা করা হচ্ছে
if ($conn->connect_error) {
    // কানেকশন ব্যর্থ হলে একটি এরর মেসেজ পাঠানো হচ্ছে
    echo json_encode(["status" => "error", "message" => "ডাটাবেস কানেকশন ব্যর্থ হয়েছে: " . $conn->connect_error]);
    exit(); // কোড চালানো বন্ধ করা হচ্ছে
}

// POST রিকোয়েস্ট থেকে JSON ডেটা গ্রহণ করা হচ্ছে
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

// pid ভ্যালু ঠিকভাবে পাওয়া গেছে কিনা তা পরীক্ষা করা হচ্ছে
if (isset($data->pid) && !empty($data->pid)) {
    // SQL ইনজেকশন থেকে সুরক্ষিত থাকার জন্য pid স্যানিটাইজ করা হচ্ছে
    $pid = $conn->real_escape_string($data->pid);

    // SQL কোয়েরি তৈরি করা হচ্ছে
    // LIMIT 1 ব্যবহার করা হয়েছে যাতে শুধুমাত্র প্রথম ম্যাচটি নিয়ে আসা হয়
    $sql = "SELECT `type` FROM `vouchers` WHERE `pid` = ? LIMIT 1";

    // Prepared Statement তৈরি করা হচ্ছে
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // pid ভ্যালু বাইন্ড করা হচ্ছে
        $stmt->bind_param("s", $pid);

        // স্টেটমেন্ট এক্সিকিউট করা হচ্ছে
        $stmt->execute();

        // ফলাফল গ্রহণ করা হচ্ছে
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // যদি pid পাওয়া যায়, তাহলে 'type' ভ্যালু আউটপুট দেওয়া হচ্ছে
            $row = $result->fetch_assoc();
            $voucher_type = $row['type'];
            echo json_encode(["status" => "success", "pid" => $pid, "type" => $voucher_type]);
        } else {
            // যদি pid খুঁজে পাওয়া না যায়
            echo json_encode(["status" => "not_found", "message" => "আপনার দেওয়া PID (" . $pid . ") খুঁজে পাওয়া যায়নি।"]);
        }

        // স্টেটমেন্ট বন্ধ করা হচ্ছে
        $stmt->close();
    } else {
        // কোয়েরি প্রস্তুত করতে সমস্যা হলে
        echo json_encode(["status" => "error", "message" => "কোয়েরি প্রস্তুত করতে সমস্যা হয়েছে।"]);
    }

} else {
    // যদি JSON রিকোয়েস্টে pid না পাঠানো হয়
    echo json_encode(["status" => "error", "message" => "দয়া করে JSON POST রিকোয়েস্টে 'pid' পাঠান। যেমন: {\"pid\":\"6\"}"]);
}

// ডাটাবেস কানেকশন বন্ধ করা হচ্ছে
$conn->close();

?>