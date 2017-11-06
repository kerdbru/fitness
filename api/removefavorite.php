<?php

if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $post_vars);
    $account_id = $post_vars['accountId'];
    $workout_id = $post_vars['workoutId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->delete_favorite($account_id, $workout_id);
}

?>