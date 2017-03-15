<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
$uid = $_POST["uid"];
if (!empty($bid)) {
	$db = new Database();
	$stmt = $db->prepare("UPDATE billContributors 
		SET recieved = 1 
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
	
	$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, 10)");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	notiLumpBill($db, $liid, $bid);
	exit();
}
?>
<?php require "wrongTurn.php";?>