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

    if (!isset($_POST["day_off_id"]) || !isset($_POST["eventdate"])) {
        echo json_encode(3);
        exit;
    } 

    include 'config.php';

    $db = connect();

    $sql = "update day_offs set date_occur=:date_occur where day_off_id=:day_off_id";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':date_occur' , $_POST['eventdate']);
    $stmt->bindValue(':day_off_id' , $_POST['day_off_id']);

    $stmt->execute();

    $res = $stmt->rowCount();

    if($res == 1)
        echo json_encode(100);
    else
        echo json_encode(0);
?>