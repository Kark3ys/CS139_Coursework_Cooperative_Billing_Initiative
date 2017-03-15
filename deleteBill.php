<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
$billName = $_POST["billName"];
if (!empty($bid)) {
	$db = new Database();
	
	
	$stmt = $db->prepare("SELECT userID from billContributors WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	
	while ($user = $result->fetchArray()) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 12, :bn)");
		$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
		$stmt->bindValue(":bn", $billName, SQLITE3_TEXT);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		notiLumpBill($db, $liid, $bid);
	}
	
	$stmt = $db->prepare("DELETE FROM billContributors WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
	
	exit();
}
?>
<?php include "wrongTurn.php"; ?>