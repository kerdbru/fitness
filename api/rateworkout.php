<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_id = $_POST['accountId'];
    $rating = $_POST['rating'];
    $workout_id = $_POST['workoutId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->rate_workout($account_id, $workout_id, $rating);
}

?>