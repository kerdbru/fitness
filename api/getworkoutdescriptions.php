<?php

$response = array();
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search = $_GET["search"];
    $type = $_GET["type"];
    $id = $_GET["accountId"];
    $favorite = $_GET["favorite"];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_workout_descriptions($search, $type, $id, $favorite);
}

echo $response;

?>