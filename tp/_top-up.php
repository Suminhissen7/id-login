<?php

require_once 'config.php';
require_once 'regulator.php';

$regulator = new Regulator();

// Log incoming raw request data
$raw_input = file_get_contents('php://input');
file_put_contents('log_requests.txt', "[" . date('Y-m-d H:i:s') . "] " . $raw_input . PHP_EOL, FILE_APPEND);

$regulator = new Regulator();

if (!$regulator->authenticate()) {
    $regulator->respond(true, 'API Key is required.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode($raw_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $regulator->respond(true, 'Something seems wrong. Please check your request.');
    }
}

if (!isset($input)) {
    $regulator->respond(true, 'This request method is not allowed.');
}

if (!array_key_exists('player_id', $input)) {
    $regulator->respond(true, 'Player ID is required.');
}

if (!array_key_exists('product_id', $input)) {
    $regulator->respond(true, 'Product ID is required.');
}

if (!array_key_exists('payment_method', $input)) {
    $regulator->respond(true, 'Payment method is required.');
}

$is_db = false;

if ($input['payment_method'] == $regulator->unipin_voucher || $input['payment_method'] == $regulator->unipin_gift_card) {
    if (!array_key_exists('db_voucher', $input) || $input['db_voucher'] == false) {
        if (!array_key_exists('serial', $input)) {
            $regulator->respond(true, 'Serial is required.');
        }

        if (!array_key_exists('pin', $input)) {
            $regulator->respond(true, 'Pin is required.');
        }
    } else {
        $vtype = $input['payment_method'] == $regulator->unipin_voucher ? 'bdmb' : 'upbd';
        $vdata = getLastVoucher($input['product_id'], $vtype);

        if (empty($vdata)) {
            $regulator->respond(true, 'Vouchers not found.');
        }

        $input['serial'] = $vdata['serial'];
        $input['pin'] = $vdata['pin'];
        $is_db = true;
    }
}

$serial = $input['serial'] ?? null;
$pin = $input['pin'] ?? null;

$regulator->setLanguage('en');

if ($regulator->setPlayerId($input['player_id'])) {
    $regulator->initiate();
    $regulator->setCsrf();
    $regulator->getProducts();

    if (!$regulator->setProductId($input['product_id'])) {
        $regulator->respond(true, 'Products ID not found.');
    }

    if (!$regulator->setPayment($input['payment_method'])) {
        $regulator->respond('Cannot set payment channel.');
    }

    $trx = $regulator->paymentInteract($serial, $pin);

    if (!empty($trx) && is_array($trx)) {
        if ($is_db) {
            deleteVoucher($serial, $pin);
        }
        $p = $regulator->getPlayer();
        $regulator->respond(false, 'success', [
            'trx_no' => $trx['trxNo'],
            'trx_status' => $trx['status'],
            'reference' => $trx['reference'],
            'payment_method' => $regulator->unipin_pm,
            'voucher_used' => [
                'serial'    => $serial,
                'pin'       => $pin
            ],
            'products_details' => $regulator->getProductDetails(),
            'player_details' => [
                'id' => $p['account_id'],
                'username' => $p['role'],
                'region' => $p['region']
            ],
            'date' => time()
        ]);
    }
} else {
    $regulator->respond(true, 'Login ID failed');
}
