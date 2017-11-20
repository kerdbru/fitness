<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $account_id = $_POST['accountId'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->create_workout($name, $type, $account_id);
}

?>