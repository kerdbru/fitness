<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->create_user($firstName, $lastName, $email, $password);
}

?>