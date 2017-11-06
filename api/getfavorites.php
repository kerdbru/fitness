<?php

$response = array();

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $account_id = $_GET['accountId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_favorites($account_id);
}

echo $response;

?>