<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
$uid = $_POST["uid"];
$ownerID = $_POST["oid"];
if (!empty($bid)) {
	$db = new Database();
	
	$ownerID = getBillOwner($db, $bid);
	$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:oid, 8)");
	$stmt->bindValue(":oid", $ownerID, SQLITE3_INTEGER);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	notiLumpBill($db, $liid, $bid);
	notiLumpUser($db, $liid, $uid);
	
	$stmt = $db->prepare("DELETE FROM billContributors 
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->execute();
	echo 'Good';
	exit();
}
?>
<?php include "wrongTurn.php";?>