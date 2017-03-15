<?php
session_start();
error_reporting(E_ALL);
ini_set(“display_errors”, 1);
require 'database.php';
require 'security.php';
$db = new Database();
$name = h($_POST["name"]);
if (!empty($_POST["name"])) {
	$stmt = $db->prepare("INSERT INTO groups(name) VALUES(:name)");
	$stmt->bindValue(":name", $name, SQLITE3_TEXT);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	
	$stmt = $db->prepare("INSERT INTO groupUserRel(groupID, userID, owner) VALUES(:gid, :uid, 1)");
	$stmt->bindValue(":uid", $_SESSION["uid"], SQLITE3_INTEGER);
	$stmt->bindValue(":gid", $liid, SQLITE3_INTEGER);
	$stmt->execute();
	
	header("Location:viewGroup.php?gid=".$liid);
}
?>
<?php include "wrongTurn.php"; ?>