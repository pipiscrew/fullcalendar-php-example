<?php
@session_start();
date_default_timezone_set("UTC");

 //invalid login attempts - kick out!
if (isset($_SESSION["invalid_login"]) && $_SESSION["invalid_login"]>3)
	exit;
    		
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_string = md5($_POST["upassword"]); //convert plain text to md5
 
    include('config.php');
 
    $db = connect();
 
    //get the dbase password for this mail
    $r = getRow($db,"select user_id,user_level from users where user_mail=? and user_password=? and user_level=?",array($_POST['umail'], $password_string,1));
 
    //^if record exists
    if ($r){
            $_SESSION['id'] = $r["user_id"];
            $_SESSION['level'] = $r["user_level"];
            $_SESSION['login_expiration'] = date("Y-m-d");
            
            header("Location: index.php");
    }
    else {
    	if (isset($_SESSION["invalid_login"]))
    		$_SESSION["invalid_login"]+=1;
    	else 
    		$_SESSION["invalid_login"]=1;
    		
        //user doesnt exist - create new
//        $sql = "INSERT INTO users (user_mail, user_password, user_level) VALUES (:user_mail, :user_password, :user_level)";
//        $stmt = $db->prepare($sql);
// 
//        $stmt->bindValue(':user_mail' , $_POST['umail']);
//        $stmt->bindValue(':user_password' , $password_string);
//        $stmt->bindValue(':user_level' , 1);
// 
//        $stmt->execute();
// 
//        $res = $stmt->rowCount();
// 
//        if($res == 1)
//            echo "User created successfully!";
//        else
//            echo "error";
    }
} 
//auto go to portal when loggedin
if (isset($_SESSION["id"])) {
	date_default_timezone_set("UTC");
	
	if ($_SESSION["login_expiration"] == date("Y-m-d"))
	{	
		header("Location: index.php");
		exit ;
	} else {
		session_destroy();
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		
		<title>Login</title>
		<link href="assets/bootstrap.login.css" rel="stylesheet">
	</head>
	
	<body>
		
    <div class="container">
 
      <form class="form-signin" method="POST" action="">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="umail" class="sr-only">Email address</label>
        <input type="email" name="umail" class="form-control" placeholder="Email address" required autofocus>
        <label for="upassword" class="sr-only">Password</label>
        <input type="password" name="upassword" id="upassword" class="form-control" placeholder="Password" required>
 
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
 
    </div> <!-- /container -->
		
	</body>
</html>