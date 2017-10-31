<?php

if($_SERVER['REQUEST_METHOD'] == 'PUT') {
    parse_str(file_get_contents("php://input"), $post_vars);
    $id = $post_vars['id'];
    $firstName = $post_vars['firstName'];
    $lastName = $post_vars['lastName'];
    $email = $post_vars['email'];
    $password = $post_vars['password'];

    require_once '../includes/DbOperation.php';
    $db = new DbOperation();
    echo $db->update_account($id, $firstName, $lastName, $email, $password);
}

?>