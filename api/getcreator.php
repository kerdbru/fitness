<?php

$response = array();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_GET['id'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_creator($id);
}

echo $response;

?>