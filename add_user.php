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

    if (!isset($_POST["event-title"])) {
        echo json_encode(3);
        exit;
    } 

    include 'config.php';

    $db = connect();

    $sql = "INSERT INTO users (user_mail, user_password, user_level) VALUES (:user_mail, :user_password, :user_level)";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':user_mail' , $_POST['event-title']);
    $stmt->bindValue(':user_password' , "_X#gza3pzab+xgRw");
    $stmt->bindValue(':user_level' , 0);

    $stmt->execute();

    $res = $stmt->rowCount();

    if($res == 1)
        echo json_encode(100);
    else
        echo json_encode(0);
?>