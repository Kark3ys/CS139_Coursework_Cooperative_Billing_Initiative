<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';
require 'generalNotificationFuncs.php';
$gid = $_POST["gid"];
$uid = $_POST["uid"];
$oid = $_POST["oid"];
$name = $_POST["name"];
if (!empty($gid)) {
	$db = new Database();
	
	$stmt = $db->prepare("UPDATE groupUserRel SET owner = 1 
		WHERE userID = :uid AND groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->execute();
	
	$stmt = $db->prepare("UPDATE groupUserRel SET owner = 0 
		WHERE userID = :oid AND groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$stmt->bindValue(":oid", $oid, SQLITE3_INTEGER);
	$stmt->execute();
	
	//Now notify the whole groupID
	
	$stmt = $db->prepare("SELECT userID FROM groupUserRel WHERE groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	while ($user = $result->fetchArray()) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 21, :name)");
		$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
		$stmt->bindValue(":name", $name, SQLITE3_TEXT);
		$stmt->execute();
		
		$liid = $db->lastInsertRowID();
		notiLumpGroup($db, $liid, $gid);
		notiLumpUser($db, $liid, $uid);
	}
	echo 'Good';
	exit();
}
?>
<?php include "wrongTurn.php";?>