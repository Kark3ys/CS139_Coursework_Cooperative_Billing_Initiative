<?php
require 'security.php';
require 'database.php';
require 'generalNotificationFuncs.php';
$name = $_POST["name"];
$gid = $_POST["gid"];
$oid = $_POST["oid"];
if (!empty($name)) {
	$db = new Database();
	$stmt = $db->prepare("SELECT userID FROM users WHERE username = :un");
	$stmt->bindValue(":un", $name, SQLITE3_TEXT);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	if ($temp) {
		$uid = $temp[0];
		//Check user in group.
		$stmt = $db->prepare("SELECT userID FROM groupUserRel WHERE userID = :uid AND groupID = :gid");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$temp = $result->fetchArray();
		if (!$temp) {
			$stmt = $db->prepare("SELECT * FROM notifications
				INNER JOIN notiGroup USING(notiID)
				WHERE userID = :uid AND groupID = :gid AND typeID = 2");
			$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
			$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
			$result = $stmt->execute();
			$temp = $result->fetchArray();
			if (!$temp) {
				//Check to see if the user has already requested to join.
				$stmt = $db->prepare("SELECT notifications.notiID FROM notifications
					INNER JOIN notiGroup USING(notiID)
					WHERE userID = :oid AND groupID = :gid AND typeID = 17");
				$stmt->bindValue(":oid", $oid, SQLITE3_INTEGER);
				$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
				$result = $stmt->execute();
				$temp = $result->fetchArray();
				if($temp) {
					//Mark that notification as read because we're dealing with it.
					$stmt = $db->prepare("UPDATE notifications SET checked=1 WHERE notiID = :nid");
					$stmt->bindValue(":nid", $temp[0], SQLITE3_INTEGER);
					$stmt->execute();
					//Then add to group.
					$stmt = $db->prepare("INSERT INTO groupUserRel(groupID, userID) VALUES(:gid, :uid)");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
					$stmt->execute();
					
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, 3)");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->execute();
					$liid = $db->lastInsertRowID();
					notiLumpGroup($db, $liid, $gid);
					echo 2;
				} else {
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, 2)");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->execute();
					$liid = $db->lastInsertRowID();
					notiLumpGroup($db, $liid, $gid);
					notiLumpUser($db, $liid, $oid);
					echo 3;
				}
				
			} else {
				echo 1;
			}		
		} else {
			echo 4;
		}
	} else {
		echo 0;
	}
	exit();
}
?>
<?php include "wrongTurn.php"; ?>