<?php
if(!isset($_SESSION)) {
	session_start();
}
require 'database.php';
$db = new Database();
$uid = $_POST["uid"];
$bid = $_POST["bid"];
$ammount = floatVal($_POST["ammount"]);

if (!empty($uid)) {
	$stmt = $db->prepare("UPDATE billContributors SET ammount = :val 
		WHERE userID = :uid AND billID = :bid");
	$stmt->bindValue(":val", $ammount, SQLITE3_FLOAT);
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->execute();
	if ($uid != $_SESSION["uid"]) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) 
			VALUES(:uid, 14)");
		$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		$stmt = $db->prepare("INSERT INTO notiBill(notiID, billID) VALUES(:liid, :bid)");
		$stmt->bindValue(":liid", $liid, SQLITE3_INTEGER);
		$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
		$stmt->execute();
	}
	exit();
}
?>
<?php include "wrongTurn.php"; ?>