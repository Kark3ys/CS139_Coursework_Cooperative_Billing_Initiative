<?php
require 'database.php';
require 'security.php';
$db = new Database();
$email = strtolower(h($_POST["email"]));
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
			$stmt = $db->prepare("UPDATE users 
				SET lastlogTS = datetime('now') 
				WHERE userID = :uid");
			$stmt->bindValue(":uid", $result["userID"], SQLITE3_INTEGER);
			$stmt->execute();
			header("Location:bills.php");
			exit();
		}
	}
	
	header("Location:login.php?err=1");
	exit();
	
}
?>
<?php include "wrongTurn.php"; ?>
