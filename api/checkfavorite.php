<?php

$response = 0;

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    $account_id = $_GET['accountId'];
    $workout_id = $_GET['workoutId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    $response = $db->check_favorite($account_id);
}

echo $response;

?>