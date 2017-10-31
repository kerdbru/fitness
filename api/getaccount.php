<?php

$response = array();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $email = $_GET['email'];
    $password = $_GET['password'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_account($email, $password);
}

echo $response;

?>