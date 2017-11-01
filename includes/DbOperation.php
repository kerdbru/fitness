<?php

class DbOperation {
    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/Config.php';
        require_once dirname(__FILE__) . '/DbConnect.php';

        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    function get_account($email, $password) {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT * FROM account WHERE email = ? and passcode = ?');
        $stmt->bind_param('ss', $email, $password);

        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return json_encode($rows);
    }

    function create_user($firstName, $lastName, $email, $password) {
        $stmt = $this->conn->prepare('INSERT INTO account(first_name, last_name, email, passcode) values(?,?,?,?)');
        $stmt->bind_param('ssss', $firstName, $lastName, $email, $password);
        $completed = $stmt->execute();
        if($completed == 1) {
            return $this->get_account($email, $password);
        }
        return '[]';
    }

    function update_account($id, $firstName, $lastName, $email, $password) {
        $stmt = $this->conn->prepare('UPDATE account SET first_name=?, last_name=?, email=?, passcode=? WHERE id=?');
        $stmt->bind_param('ssssi', $firstName, $lastName, $email, $password, $id);

        return $stmt->execute();
    }

    function get_workout_descriptions() {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT wd.id, wd.name, wt.name AS type, a.first_name, a.id AS account_id 
                                      FROM workout_description AS wd 
                                      INNER JOIN workout_type AS wt ON wd.workout_type_id=wt.id 
                                      INNER JOIN account AS a ON wd.account_id=a.id
                                      WHERE wd.visible=1');

        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {

            $stmt = $this->conn->prepare('SELECT count(*) as number from ratings where workout_description_id = ?');
            $stmt->bind_param('i', row['id']);
            $stmt->execute();
            $row['rating_count'] = $stmt->get_result();


            $stmt = $this->conn->prepare('SELECT sum(score) as number from ratings where workout_description_id = ?');
            $stmt->bind_param('i', row['id']);
            $stmt->execute();
            $row['rating_sum'] = $stmt->get_result();

            $rows[] = $row;
        }

        return json_encode($rows);
    }
}

?>