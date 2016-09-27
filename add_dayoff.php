<?php
    @session_start();

    if (!isset($_SESSION["id"])) {
        echo json_encode(1);
        exit;
    }
    else {
        date_default_timezone_set("UTC");

        if ($_SESSION["login_expiration"] != date("Y-m-d"))
        {	
            session_destroy();
            echo json_encode(2);
            exit;
        }
    }

    if (!isset($_POST["userid"]) || !isset($_POST["eventdate"]) || !isset($_POST["typeid"]) || !isset($_POST["comment"])) {
        echo json_encode(3);
        exit;
    } 

    include 'config.php';

    $db = connect();

    $sql = "INSERT INTO day_offs (day_off_type, user_id, date_occur, comment) VALUES (:day_off_type, :user_id, :date_occur, :comment)";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':day_off_type' , $_POST['typeid']);
    $stmt->bindValue(':user_id' , $_POST['userid']);
    $stmt->bindValue(':date_occur' , $_POST["eventdate"]);
    $stmt->bindValue(':comment' , $_POST["comment"]);

    $stmt->execute();

    $res = $stmt->rowCount();

    if($res == 1)
        echo json_encode(100);
    else
        echo json_encode(0);
?>