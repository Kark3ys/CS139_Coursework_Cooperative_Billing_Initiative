<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
$uid = $_POST["uid"];
if (!empty($bid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT name FROM bills WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	$temp = $result->fetchArray();
	$name = $temp[0];
	
	$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 20, :name)");
	$stmt->bindValue(":uid", $uid, SQLITE3_INTEGER);
	$stmt->bindValue(":name", $name, SQLITE3_TEXT);
	$stmt->execute();
	$liid = $db->lastInsertRowID();
	notiLumpBill($db, $liid, $bid);
	
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