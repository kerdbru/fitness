<?php

$response = array();

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $workout_description_id = $_GET['workoutId'];
    $account_id = $_GET['accountId'];
    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->get_exercise_order($workout_description_id, $account_id);
}

echo $response;

?>