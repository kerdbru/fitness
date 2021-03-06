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

    function get_workout_descriptions($search, $type, $id, $favorite) {
        $rows = array();
        $arguments = array();
        $types = 's';
        $query = 'SELECT wd.id, wd.name, wt.name AS type, a.first_name, a.id AS account_id 
                                      FROM workout_description AS wd 
                                      INNER JOIN workout_type AS wt ON wd.workout_type_id=wt.id 
                                      INNER JOIN account AS a ON wd.account_id=a.id
                                      WHERE wd.visible=1 and wd.name LIKE ?';

        $arguments[] = "%".$search."%";
        if($type > 0) {
            $types = $types . 'i';
            $arguments[] = $type;
            $query = $query . ' and wd.workout_type_id=?';
        }
        if($id > 0) {
            $types = $types . 'i';
            $arguments[] = $id;
            $query = $query . ' and wd.account_id=?';
        }
        $query = $query . ' ORDER BY wd.name';

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$arguments);
        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $stmt = $this->conn->prepare('SELECT count(*) AS number FROM favorites WHERE account_id=? AND workout_description_id = ?');
            $stmt->bind_param('ii', $favorite, $row["id"]);
            $stmt->execute();
            $row['favorite'] = (int)mysqli_fetch_assoc($stmt->get_result())['number'];

            $stmt = $this->conn->prepare('SELECT count(*) as number from ratings where workout_description_id = ?');
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $row['rating_count'] = (int)mysqli_fetch_assoc($stmt->get_result())['number'];


            $stmt = $this->conn->prepare('SELECT sum(score) as number from ratings where workout_description_id = ?');
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $row['rating_sum'] = (int)mysqli_fetch_assoc($stmt->get_result())['number'];

            $rows[] = $row;
        }

        return json_encode($rows);
    }

    function get_exercise_order($workout_description_id, $account_id) {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT wo.id, wo.position, wo.amount, wo.weight, wo.sets, l.name as label, 
                                             e.name as name, e.description as description, e.id as exercise_id 
                                        FROM workout_order as wo
                                        inner join label as l on wo.label_id=l.id
                                        inner join exercise as e on wo.exercise_id=e.id
                                        WHERE wo.workout_description_id = ? AND wo.account_id = ?
                                        ORDER BY wo.position');
        $stmt->bind_param('ii', $workout_description_id, $account_id);
        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return json_encode($rows);
    }

    function rate_workout($account_id, $workout_id, $rating) {
        $stmt = $this->conn->prepare('INSERT INTO ratings (account_id, workout_description_id, score) 
                                      VALUES(?,?,?) ON DUPLICATE KEY UPDATE score = ?');
        $stmt->bind_param('iiii', $account_id, $workout_id, $rating, $rating);

        return $stmt->execute();
    }

    function get_rating($account_id, $workout_id) {
        $stmt = $this->conn->prepare('SELECT score from ratings where account_id = ? and workout_description_id = ?');
        $stmt->bind_param('ii', $account_id, $workout_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if($row = mysqli_fetch_assoc($result)) {
            return (int)$row['score'];
        }
        return 0;
    }

    function add_favorite($account_id, $workout_id, $original_account_id) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS number FROM workout_description
                                      WHERE account_id=? AND id=?');
        $stmt->bind_param('ii', $account_id, $workout_id);
        $stmt->execute();

        if(mysqli_fetch_assoc($stmt->get_result())['number'] == 0) {
            $stmt = $this->conn->prepare('SELECT * from workout_order WHERE account_id = ? AND workout_description_id = ?');
            $stmt->bind_param('ii', $original_account_id, $workout_id);
            $stmt->execute();

            $result = $stmt->get_result();
            while ($row = mysqli_fetch_assoc($result)) {
                $stmt = $this->conn->prepare('INSERT INTO workout_order (workout_description_id, account_id, position, 
                                              exercise_id, label_id, amount, weight, sets) 
                                          VALUES(?,?,?,?,?,?,?,?)');
                $stmt->bind_param('iiiiiiii', $row['workout_description_id'], $account_id, $row['position'], $row['exercise_id'],
                    $row['label_id'], $row['amount'], $row['weight'], $row['sets']);
                $stmt->execute();
            }
        }

        $stmt = $this->conn->prepare('INSERT INTO favorites (account_id, workout_description_id) 
                                      VALUES(?,?)');
        $stmt->bind_param('ii', $account_id, $workout_id);
        return $stmt->execute();
    }

    function delete_favorite($account_id, $workout_id) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS number FROM workout_description
                                      WHERE account_id=? AND id=?');
        $stmt->bind_param('ii', $account_id, $workout_id);
        $stmt->execute();

        if(mysqli_fetch_assoc($stmt->get_result())['number'] == 0) {
            $stmt = $this->conn->prepare('DELETE FROM workout_order WHERE workout_description_id=? AND account_id=?');
            $stmt->bind_param('ii', $workout_id, $account_id);
            $stmt->execute();
        }

        $stmt = $this->conn->prepare('DELETE FROM favorites where 
                                      account_id = ? AND workout_description_id = ?');
        $stmt->bind_param('ii', $account_id, $workout_id);
        return $stmt->execute();
    }

    function get_favorites($account_id) {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT wdwt.name, wdwt.type, wdwt.id, wdwt.account_id from favorites as f 
                                      inner join (SELECT wd.id, wd.name, wt.name  as type, wd.account_id from workout_description as wd 
                                      INNER JOIN workout_type as wt on wd.workout_type_id=wt.id) as wdwt on f.workout_description_id=wdwt.id 
                                      where f.account_id = ? ORDER BY wdwt.name');
        $stmt->bind_param('i', $account_id);

        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return json_encode($rows);
    }

    function check_favorite($account_id, $workout_id) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS number FROM favorites WHERE
                                      account_id = ? AND workout_description_id = ?');
        $stmt->bind_param('ii', $account_id, $workout_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if($row = mysqli_fetch_assoc($result)) {
            return (int)$row['number'];
        }
        return 0;
    }

    function get_exercises($search) {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT name, id, description FROM exercise where name LIKE ?');

        $query = "%".$search."%";
        $stmt->bind_param("s", $query);

        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return json_encode($rows);
    }

    function get_creator($id) {
        $stmt = $this->conn->prepare('SELECT first_name, last_name FROM account WHERE id = ?');
        $stmt->bind_param('i', $id);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = mysqli_fetch_assoc($result)) {
            return json_encode($row);
        }

        return "";
    }

    function create_workout($name, $type, $account_id) {
        $stmt = $this->conn->prepare('INSERT INTO workout_description(name, workout_type_id, account_id) VALUES(?,?,?)');
        $stmt->bind_param('sii', $name, $type, $account_id);
        $stmt->execute();
        return $stmt->insert_id;
    }

    function add_workout_item($workout_id, $account_id, $position, $exercise_id, $label_id, $amount, $weight, $sets) {
        if($weight == 0) {
            $weight = null;
        }
        $stmt = $this->conn->prepare('INSERT INTO workout_order(workout_description_id, account_id, position, exercise_id, label_id, amount, weight, sets) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->bind_param('iiiiiiii', $workout_id, $account_id, $position, $exercise_id, $label_id, $amount, $weight, $sets);
        return $stmt->execute();
    }

    function get_labels() {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT name as text, id from label');
        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return json_encode($rows);
    }

    function get_users() {
        $rows = array();
        $stmt = $this->conn->prepare('SELECT id, first_name, last_name FROM account');

        $stmt->execute();

        $result = $stmt->get_result();
        while($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return json_encode($rows);
    }

    function get_stats($id) {
        $response = Array();
        $stmt = $this->conn->prepare("SELECT count(*) as number, CONVERT(ifnull(sum(r.score),0), UNSIGNED INTEGER) as total 
                                      from ratings as r INNER join workout_description as wd on 
                                      r.workout_description_id = wd.id where wd.account_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $response = mysqli_fetch_assoc($stmt->get_result());

        $stmt = $this->conn->prepare("SELECT count(*) as creations from workout_description where account_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $response["creations"] = mysqli_fetch_assoc($stmt->get_result())["creations"];

        $stmt = $this->conn->prepare("SELECT count(*) as favorites from favorites as f inner join 
                                      workout_description as wd on f.workout_description_id=wd.id 
                                      where wd.account_id = ? and not f.account_id = ?");
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $response["favorites"] = mysqli_fetch_assoc($stmt->get_result())["favorites"];

        return json_encode($response);
    }
}
?>