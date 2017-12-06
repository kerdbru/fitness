<?php

$response = array();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_users();
}

echo $response;

?>