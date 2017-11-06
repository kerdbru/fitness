<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_id = $_POST['accountId'];
    $workout_id = $_POST['workoutId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->add_favorite($account_id, $workout_id);
}

?>