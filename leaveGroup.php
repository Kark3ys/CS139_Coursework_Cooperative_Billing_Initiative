<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$gid = $_POST["gid"];
$uid = $_POST["uid"];
$oid = $_POST["oid"];
if (!empty($gid)) {
	$db = new Database();
	
	$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 4)");
	$stmt->bindValue(":oid", $oid, SQLITE3_INTEGER);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	notiLumpGroup($db, $liid, $gid);
	notiLumpUser($db, $liid, $uid);
	
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