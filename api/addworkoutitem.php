<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $workout_id = $_POST["workoutId"];
    $account_id = $_POST["accountId"];
    $position = $_POST["position"];
    $exercise_id = $_POST["exerciseId"];
    $label_id = $_POST["labelId"];
    $amount = $_POST["amount"];
    $weight = $_POST["weight"];
    $sets = $_POST["sets"];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->add_workout_item($workout_id, $account_id, $position, $exercise_id, $label_id, $amount, $weight, $sets);
}

?>

