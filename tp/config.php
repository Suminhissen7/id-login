<?php

$host = 'mysql-tobd.alwaysdata.net';
$dbname = 'tobd_api';
$username = 'tobd';
$password = 'shihab067';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function getLastVoucher($pid, $type) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM vouchers 
            WHERE pid = :pid AND type = :type
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->bindValue(':pid', $pid, PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->execute();

        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        return $voucher ?: null; // Mengembalikan null jika tidak ada data
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

function deleteVoucher($serial, $pin) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            DELETE 
            FROM vouchers 
            WHERE serial = :serial AND pin = :pin
        ");
        $stmt->bindValue(':serial', $serial, PDO::PARAM_STR);
        $stmt->bindValue(':pin', $pin, PDO::PARAM_STR);

        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}