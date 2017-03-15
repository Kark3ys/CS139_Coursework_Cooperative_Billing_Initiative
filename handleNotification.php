<?php
session_start();
$uid = $_SESSION["uid"];
require 'database.php';
require "generalNotificationFuncs.php";
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
				$ownerID = getGroupOwner($db, $gid);
				if($nReply != 0) {
					$stmt = $db->prepare("INSERT INTO groupUserRel(userID, groupID) VALUES(:uid, :gid)");
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
					$stmt->execute();
					
					//Build Notifications
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 3)");
				} else {
					//Invite Rejected, tell owner
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 16)");
				}
				$stmt->bindValue(":oid", $ownerID, SQLITE3_INTEGER);
				$stmt->execute();
				$liid = $db->lastInsertRowID();
				notiLumpGroup($db, $liid, $gid);
				notiLumpUser($db, $liid, $uid);
			break;
			
			case 7: //Contribution Invite
				$ownerID = getBillOwner($db, $bid);
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
				notiLumpBill($db, $liid, $bid);
				notiLumpUser($db, $liid, $uid);
			break;
			
			case 15: //Contribute As Group Invite
				$ownerID = getBillOwner($db, $bid);
				if ($nReply != 0) {
					$stmt = $db->prepare("INSERT INTO billContributors(billID, userID, groupID)
						VALUES(:bid, :uid, :gid)");
					$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
					$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
					$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
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
				notiLumpBill($db, $liid, $bid);
				notiLumpUser($db, $liid, $uid);
				notiLumpGroup($db, $liid, $gid);
			break;
			
			case 17: //Group Membership Requested
			//This is the owner accepting/rejecting someone wanting to join
			//Therefore $uid is the owner id, and $suid is who we need to target the end noti to.
				if ($nReply != 0) {
					$stmt = $db->prepare("INSERT INTO groupUserRel(userID, groupID) VALUES(:suid, :gid)");
					$stmt->bindValue(":suid", $suid, SQLITE3_INTEGER);
					$stmt->bindValue(":gid", $gid, SQLITE3_INTEGER);
					$stmt->execute();
					
					//Build Notifications.
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:suid, 3)");
				} else {
					//Tell suid that their request is rejetced.
					$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:suid, 16)");
				}
				$stmt->bindValue(":suid", $suid, SQLITE3_INTEGER);
				$stmt->execute();
				$liid = $db->lastInsertRowID();
				notiLumpGroup($db, $liid, $gid);
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