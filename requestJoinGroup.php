<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$gid = $_POST["gid"];
$uid = $_POST["uid"];
$oid = $_POST["oid"];
if (!empty($gid)) {
	$db = new Database();
	$stmt = $db->prepare("SELECT groupID FROM groupUserRel
		WHERE userID = :uid AND groupID = :gid");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$tempRes = $stmt->execute();
	$temp = $tempRes->fetchArray();
	if (!$temp) {
		$stmt = $db->prepare("SELECT notifications.notiID FROM notifications
			INNER JOIN notiGroup USING(notiID)
			INNER JOIN notiUser USING(notiID)
			WHERE groupID = :gid AND secondUserID = :uid AND userID = :oid AND typeID=17");
		$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->bindValue(":oid", $oid, SQLITE3_INTEGER);
		$tempRes = $stmt->execute();
		$temp = $tempRes->fetchArray();
		if (!$temp) {
			$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 17)");
			$stmt->bindValue(":oid", $oid, SQLITE3_INTEGER);
			$stmt->execute();
			$liid = $db->lastInsertRowID();
			notiLumpGroup($db, $liid, $gid);
			notiLumpUser($db, $liid, $uid);
			echo 2;
		} else {
			echo 1;
		}
	} else {
		echo 0;
	}
	exit();
}
?>
<?php require "wrongTurn.php"; ?>