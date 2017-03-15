<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$gid = $_POST["gid"];
if (!empty($gid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT name FROM groups WHERE groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	$name = $temp[0];
	
	$stmt = $db->prepare("SELECT userID from groupUserRel WHERE groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	
	while ($user = $result->fetchArray()) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 6, :gn)");
		$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
		$stmt->bindValue(":gn", $name, SQLITE3_TEXT);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		notiLumpGroup($db, $liid, $gid);
	}
	
	$stmt = $db->prepare("DELETE FROM groupUserRel WHERE groupID = :gid");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$stmt->execute();
	
	exit();
}
?>
<?php include "wrongTurn.php"; ?>