<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_id = (int)$_POST['accountId'];
    $workout_id = (int)$_POST['workoutId'];

    echo gettype($account_id);

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->add_favorite($account_id, $workout_id);
}

?>