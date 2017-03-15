<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
$total = floatVal($_POST["total"]);
if (!empty($bid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT userID from billContributors WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	
	while ($user = $result->fetchArray()) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID, custmsg) VALUES(:uid, 13, :msg)");
		$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
		$msg = "New Total: £".number_format($total, 2);
		$stmt->bindValue(":msg", $msg, SQLITE3_TEXT);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		notiLumpBill($db, $liid, $bid);
	}
	
	$stmt = $db->prepare("UPDATE bills 
		SET total = :total, editTS = datetime('now') 
		WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":total", $total, SQLITE3_FLOAT);
	$stmt->execute();
	exit();
}
?>
<?php include "wrongTurn.php";?>