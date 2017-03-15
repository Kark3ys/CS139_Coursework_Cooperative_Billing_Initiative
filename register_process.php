<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';

//Removing special cahracters from inputs:
require "security.php";
$user = h($_POST["username"]);
$email = h($_POST["email"]);
$pass = h($_POST["pass"]);
$name = h($_POST["name"]);

if(!empty($user)) {
	//Check for duplicate email/username.
	$db = new Database();
	$stmt = $db->prepare("SELECT * FROM users WHERE username=:user");
	$stmt->bindValue(":user", $user, SQLITE3_TEXT);
	$sqlResult = $stmt->execute();
	$result = $sqlResult->fetchArray();
	if(!empty($result)) {
		header("Location:register.php?err=1");
		exit();
	}
  
	$stmt = $db->prepare("SELECT * FROM users WHERE email=:email");
	$stmt->bindValue(":email", $email, SQLITE3_TEXT);
	$sqlResult = $stmt->execute();
	$result = $sqlResult->fetchArray();
	if(!empty($result)) {
		header("Location:register.php?err=2");
		exit();
	}
	//$pass='A'; /*Take this out later*/
	$salt = sha1(time());
	$encPass = sha1($salt."--".$pass);
	$stmt = $db->prepare("INSERT INTO users(username, realname, pass, salt, email)
		VALUES(:user, :name, :pass, :salt, :email);");
	$stmt->bindValue(":user", $user, SQLITE3_TEXT);
	$stmt->bindValue(":name", $name, SQLITE3_TEXT);
	$stmt->bindValue(":pass", $encPass, SQLITE3_TEXT);
	$stmt->bindValue(":salt", $salt, SQLITE3_TEXT);
	$stmt->bindValue(":email", $email, SQLITE3_TEXT);
	$stmt->execute();
	$result = $db->lastInsertRowID();
	session_start();
	$_SESSION["uid"] = $result;
	header("Location:bills.php");
	exit();
	
}
?>
<?php include wrongTurn.php?>
