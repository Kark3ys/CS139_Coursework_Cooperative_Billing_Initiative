<?php
if(!isset($_SESSION)) {
	session_start();
}
require 'database.php';
require 'generalNotificationFuncs.php';
$db = new Database();
$uid = $_POST["uid"];
$bid = $_POST["bid"];
$ammount = floatVal($_POST["ammount"]);

if (!empty($uid)) {
	$stmt = $db->prepare("UPDATE billContributors 
		SET ammount = :val, paid = 0, recieved = 0
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":val", $ammount, SQLITE3_FLOAT);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
	if ($uid != $_SESSION["uid"]) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) 
			VALUES(:uid, 14, :msg)");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->bindValue(":msg", "New Contribution: £".number_format($ammount, 2), SQLITE3_TEXT);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		echo $liid;
		notiLumpBill($db, $liid, $bid);
	}
	exit();
}
?>
<?php include "wrongTurn.php"; ?>