<?php  
/**
* @link https://pipiscrew.com
* @copyright Copyright (c) 2016 PipisCrew
*/
 
function connect_mysql() {
    $mysql_hostname = "localhost";
    $mysql_user = "";
    $mysql_password = "";
    $mysql_database = "test"; 
     
    $dbh = new PDO("mysql:host=$mysql_hostname;dbname=$mysql_database", $mysql_user, $mysql_password, 
  array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
  ));
 
  return $dbh;
}
 
function connect() {
    //if doesnt exist, will created.
    $dbh = new PDO('sqlite:dbase.db');
	//$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	
	//check if table has records, if not create table
	$d = getScalar($dbh, "select count(*) from users",null);
	if ($d==0)
	{
		executeSQL($dbh, "CREATE TABLE [users] (user_id INTEGER PRIMARY KEY, user_mail TEXT, user_password TEXT, user_level INTEGER)", null);
		executeSQL($dbh, "your other tables here?",null);
		
		//read&write only server (user cant download the dbase)
		chmod("dbase.db", 0600);
	}
	//check if table has records, if not create table
    return $dbh;
}
 
function getScalar($db, $sql, $params) {
    if ($stmt = $db -> prepare($sql)) {
 
        $stmt->execute($params);
 
        return $stmt->fetchColumn();
    } else
        return 0;
}
 
function getRow($db, $sql, $params) {
    if ($stmt = $db -> prepare($sql)) {
 
        $stmt->execute($params);
 
        return $stmt->fetch();
    } else
        return 0;
}
 
function getSet($db, $sql, $params) {
    if ($stmt = $db -> prepare($sql)) {
 
        $stmt->execute($params);
 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
//      return $stmt->fetchAll();
    } else
        return 0;
}
 
function executeSQL($db, $sql, $params) {
    if ($stmt = $db -> prepare($sql)) {
 
        $stmt->execute($params);
 
        return $stmt->rowCount();
    } else
        return false;
}
?>