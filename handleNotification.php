<?php
session_start();
$uid = $_SESSION["uid"];
require 'database.php';
require 'security.php';
$nid = $_POST["nid"];
$nType = $_POST["nType"];
$nReply = $_POST["nReply"];
$bid = $_POST["bid"];
$gid = $_POST["gid"];
$suid = $_POST["suid"];
if (!empty($nid)) {
	$db = new Database();
	if (!empty($nType)) {
		switch ($nType) {
			case 2:	//Group Invite
			break;
			case 7: //Contribution Invite
				$stmt = $db->prepare("SELECT userID FROM billContributors
				WHERE billID = :bid AND owner = 1");
				$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
				$result = $stmt->execute();
				$temp = $result->fetchArray();
				$ownerID = $temp[0];
				
				if ($nReply != 0) {
					$stmt = $db->prepare("INSERT INTO billContributors(billID, userID)
						VALUES(:bid, :uid)");
					$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->execute();
					
					//Build Notifications
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 3)");
				} else {
					//Invite Rejected, tell the owner.
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 16)");
					
				}
				$stmt->bindValue(":oid", $ownerID, SQLITE3_INTEGER);
				$stmt->execute();
				$liid = $db->lastInsertRowID();
				$stmt = $db->prepare("INSERT INTO notiUser(notiID, secondUserID) VALUES(:liid, :uid)");
				$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
				$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
				$stmt->execute();
				$stmt = $db->prepare("INSERT INTO notiBill(notiID, billID) VALUES(:liid, :bid)");
				$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
				$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
				$stmt->execute();
			break;
			case 15: //Contribute As Group Invite
			break;
			case 17: //Group Invite Requested
			break;
		}
	}
	//Just mark as read.
	$stmt = $db->prepare("UPDATE notifications SET checked = 1 WHERE notiID = :nid");
	$stmt->bindValue(":nid", $nid, SQLITE3_INTEGER);
	$stmt->execute();
	exit();
}
?>
<?php include "wrongTurn.php"; ?>