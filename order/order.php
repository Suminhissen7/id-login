<?php
$conn = new mysqli("mysql-tobd.alwaysdata.net", "tobd", "shihab067", "tobd_api");

// ইনপুট ভ্যারিয়েবল
$psid = $_POST['psid'] ?? '';
$player_id = $_POST['player_id'] ?? '';
$pid = $_POST['pid'] ?? '';
$trx = $_POST['trx'] ?? '';
$pay_m = $_POST['pay_m'] ?? '';

// চেক করুন সব ইনপুট আছে কি না
if (empty($psid) || empty($player_id) || empty($pid) || empty($trx) || empty($pay_m)) {
    echo "সব তথ্য পূরণ করুন: psid, player_id, pid, trx, pay_m";
    exit;
}

// অর্ডার নাম্বার তৈরি
$order_no = generateOrderNo($conn);

$status = 'pending';
$datetime = date("Y-m-d H:i:s");
$msg = 'অর্ডার তৈরি হয়েছে';

// ইনসার্ট করুন
$stmt = $conn->prepare("INSERT INTO orders (order_no, psid, player_id, pid, trx, pay_m, status, datetime, msg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $order_no, $psid, $player_id, $pid, $trx, $pay_m, $status, $datetime, $msg);
$stmt->execute();

echo "আপনার অর্ডার রিসিভ হয়েছে। অর্ডার নাম্বার: $order_no";

// ব্যাকগ্রাউন্ড প্রসেস
//exec("php process_order.php $order_no > /dev/null 2>/dev/null &");

// order_no জেনারেটর ফাংশন
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
