<?php
require 'database.php';
require 'security.php';
$db = new Database();
$email = h($_POST["email"]);
$pass = h($_POST["pass"]);
if(!empty($email)) {
	$stmt = $db->prepare("SELECT * FROM users WHERE email=:email;");
	$stmt->bindValue(":email", $email, SQLITE3_TEXT);
	$sqlResult = $stmt->execute();
	$result = $sqlResult->fetchArray();
	if(!empty($result)) {
		if(sha1($result["salt"]."--".$pass) == $result["pass"]) {
			session_start();
			$_SESSION["uid"] = $result["userID"];
			header("Location:bills.php?uid=");
			die();
		}
	}
	
	header("Location:login.php?err=1");
	die();
	
}
?>
<?php include "wrongTurn.php"; ?>
