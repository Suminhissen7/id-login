<?php

require_once 'regulator.php';

$regulator = new Regulator();

if (!$regulator->authenticate()) {
    $regulator->respond(true, 'API Key is required. ');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

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

$regulator->setLanguage('en');
if ($regulator->setPlayerId($input['player_id'])) {
    $regulator->initiate();
    $regulator->setCsrf();
    $regulator->getProducts();
    $products = $regulator->getAllProducts();
    if ($products !== null) {
        $regulator->respond(false, 'success', $products);
    } else {
        $regulator->respond(true, 'Cannot get products.');
    }
} else {
    $regulator->respond(true, 'Login ID failed');
}