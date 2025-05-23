<?php
$conn = new mysqli("mysql-tobd.alwaysdata.net", "tobd", "shihab067", "tobd_api");

// JSON ইনপুট ধরুন
$input = json_decode(file_get_contents("php://input"), true);

// ইনপুট ভ্যারিয়েবলগুলো ঠিকভাবে ধরুন
$psid = $input['psid'] ?? '';
$player_id = $input['player_id'] ?? '';
$pid = $input['pid'] ?? '';
$trx = $input['trx'] ?? '';
$pay_m = $input['pay_m'] ?? '';

// চেক করুন সব ইনপুট আছে কি না
if (empty($psid) || empty($player_id) || empty($pid) || empty($trx) || empty($pay_m)) {
    echo "সব তথ্য পূরণ করুন: psid, player_id, pid, trx, pay_m";
    exit;
}

// order_no তৈরি
$order_no = generateOrderNo($conn);
$status = 'pending';
$datetime = date("Y-m-d H:i:s");
$msg = 'অর্ডার তৈরি হয়েছে';

// ডাটাবেজে ইনসার্ট
$stmt = $conn->prepare("INSERT INTO orders (order_no, psid, player_id, pid, trx, pay_m, status, datetime, msg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $order_no, $psid, $player_id, $pid, $trx, $pay_m, $status, $datetime, $msg);
$stmt->execute();

echo "আপনার অর্ডার রিসিভ হয়েছে। অর্ডার নাম্বার: $order_no";

// ব্যাকগ্রাউন্ড প্রসেস চালান
exec("php process_order.php $order_no > /dev/null 2>/dev/null &");

// ফাংশনঃ order_no জেনারেট করা
function generateOrderNo($conn) {
    $prefix = "tobd";
    $result = $conn->query("SELECT order_no FROM orders WHERE order_no LIKE '$prefix%' ORDER BY order_no DESC LIMIT 1");

    if ($result->num_rows > 0) {
        $lastOrder = $result->fetch_assoc()['order_no'];
        $num = intval(substr($lastOrder, strlen($prefix)));
        $num++;
    } else {
        $num = 6788;
    }

    return $prefix . $num;
}
?>
