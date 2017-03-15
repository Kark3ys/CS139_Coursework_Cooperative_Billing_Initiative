<?php
require 'database.php';
require 'generalNotificationFuncs.php';
$bid = $_POST["bid"];
if (!empty($bid)) {
	$db = new Database();
	
	$stmt = $db->prepare("SELECT userID from billContributors WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	
	while ($user = $result->fetchArray()) {
		$stmt = $db->prepare("INSERT INTO notifications(userID, typeID) VALUES(:uid, :type)");
		$stmt->bindValue(":uid", $user[0], SQLITE3_INTEGER);
		if ($_POST["compState"] != 0) $type = 11; else $type = 19;
		$stmt->bindValue(":type", $type, SQLITE3_INTEGER);
		$stmt->execute();
		$liid = $db->lastInsertRowID();
		notiLumpBill($db, $liid, $bid);
	}
	
	$stmt = $db->prepare("UPDATE bills 
		SET complete = :cs, editTS = datetime('now') 
		WHERE billID = :bid");
	$stmt->bindValue(":bid", $bid, SQLITE3_INTEGER);
	$stmt->bindValue(":cs", $_POST["compState"], SQLITE3_INTEGER);
	$stmt->execute();
	
	exit();
}
?>
<?php include "wrongTurn.php"; ?>