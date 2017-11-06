<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $rating = $_POST['rating'];
    $workout_id = $_POST['workoutId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->rate_workout($id, $workout_id, $rating);
}

?>