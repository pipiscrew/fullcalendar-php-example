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

    if (!isset($_POST["user_id"])) {
        echo json_encode(3);
        exit;
    } 

    include 'config.php';

    $db = connect();

    //events
    $sql = "delete from day_offs where user_id=:user_id";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':user_id' , $_POST['user_id']);

    $stmt->execute();

    $res = $stmt->errorCode();

    //user
    $sql = "delete from users where user_id=:user_id";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':user_id' , $_POST['user_id']);

    $stmt->execute();

    $res = $stmt->errorCode();

    if($res == "0000")
        echo json_encode(100);
    else
        echo json_encode(0);
?>