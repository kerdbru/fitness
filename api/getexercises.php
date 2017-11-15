<?php

$response = array();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search = $_GET["search"];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_exercises($search);
    echo sizeof($response);
}

echo $response;

?>