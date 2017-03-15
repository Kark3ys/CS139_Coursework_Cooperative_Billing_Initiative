<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';
require 'generalNotificationFuncs.php';
$gid = $_POST["gid"];
$uid = $_POST["uid"];
if (!empty($gid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT name FROM groups WHERE groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	$name = $temp[0];
	
	$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 5, :name)");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":name", $name, SQLITE3_TEXT);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	notiLumpGroup($db, $liid, $gid);
	
	$stmt = $db->prepare("DELETE FROM groupUserRel 
		WHERE userID = :uid AND groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->execute();
	echo 'Good';
	exit();
}
?>
<?php include "wrongTurn.php";?>