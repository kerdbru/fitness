<?php
$response = array();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploaddir = '../uploads/account/';
    $filename = $_FILES['image']['name'];
    print_r($_FILES);

    if(array_key_exists('id', $_POST)) {
        $uploadfile = $uploaddir . $_POST['id'];
    }
    else {
        echo 'no id specified';
        return;
    }
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfile)) {
        echo "File is valid, and was successfully uploaded.\n";
    }
    else {
        echo 'upload failed';
    }
}
?>