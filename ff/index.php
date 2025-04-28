<?php

require_once 'garena.php';
require_once 'db.php';

$garena = new Garena();
$db = new DB();

if (!$garena->authenticate()) {
    $garena->respond(true, 'invalid_requests');
}

if (!isset($_GET['id']) || empty($_GET['id'])) { 	
    $garena->respond(true, 'id_field_required');
}

if (!is_numeric($_GET['id'])) {
    $garena->respond(true, 'numeric_id_type');
}

if (strlen($_GET['id']) < 8 || strlen($_GET['id']) > 12) {
    $garena->respond(true, 'id_not_found');
}


$garena->init();
$garena->setDatadome();
if ($db->containsUserId($_GET['id'])) {
    $session_key = $db->getSessionKey($_GET['id']);
    $garena->checkPlayerId($_GET['id'], $session_key);
} else {
    $garena->setPlayerId($_GET['id']);
}
$db->saveUserId($_GET['id'], $garena->cookies['session_key']);
$garena->respond(false, 'success', array_merge($garena->player, ['cookies' => $garena->cookies]));