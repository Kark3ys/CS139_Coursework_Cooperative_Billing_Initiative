<?php
require 'database.php';
require 'security.php';
require 'generalNotificationFuncs.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$bid = $_POST["bid"];
$gid = $_POST["gid"];
if (!empty($bid)) {
	$db = new Database();
	$ownerID = getBillOwner($db, $bid);
	$stmt = $db->prepare("SELECT DISTINCT userID FROM groupUserRel
		WHERE groupID = :gid ");
	$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	while ($user = $result->fetchArray()) 
		if ($user[0] != $ownerID) {
			$stmt = $db->prepare("SELECT * FROM notifications
					INNER JOIN notiBill ON notifications.notiID = notiBill.notiID
					WHERE userID = :uid AND billID = :bid AND typeID IN (7, 15)");
			$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
			$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
			$tempres = $stmt->execute();
			$temp = $tempres->fetchArray();
			if(!$temp) {
				$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, 15)");
				$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
				$stmt->execute();
				$liid = $db->lastInsertRowID();
				notiLumpBill($db, $liid, $bid);
				notiLumpGroup($db, $liid, $gid);
			}
	}
	exit();
}
?>
<?php include "wrongTurn.php"; ?>